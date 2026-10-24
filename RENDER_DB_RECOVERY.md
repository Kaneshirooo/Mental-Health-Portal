# Recovering the database after the Render free Postgres expired

## What actually broke

The web service is fine. The **database** is gone.

```
SQLSTATE[08006] [7] could not translate host name "dpg-dagkqb0473hc73bhd4d0-a"
to address: Name or service not known
```

`dpg-dagkqb0473hc73bhd4d0-a` was the internal hostname of your Render PostgreSQL
instance. Render deletes free Postgres instances roughly 30 days after they are
created, so that hostname no longer exists in DNS. Nothing on the app side is
broken — it simply has no database to talk to.

Data written to that database after your last local dump is **not recoverable**.

## What we still have

| Source | Contents |
| --- | --- |
| `mental_health_db (29).sql` (Sep 25, 2026) | MySQL dump, exact snapshot of the local database |
| Local XAMPP `mental_health_db` | Identical row counts to that dump — same data, and queryable |

Verified identical: both contain 9 users, 85 assessments, 1601 call messages,
1725 student responses, 380 notifications, 116 emergency calls, 320 session logs.

## The plan

1. Create a new Render Postgres instance.
2. Point the web service at it.
3. Redeploy — Laravel's migrations rebuild the schema automatically.
4. Copy the data across with `tools/restore-mysql-to-postgres.php`.

You do **not** need to convert the `.sql` dump. The schema comes from the
migrations and the rows come from the local MySQL database.

---

## Step 0 — Check whether the data is still recoverable

**Do this before creating anything.** Render does not delete an expired free
database immediately:

> Free Render Postgres databases expire 30 days after creation. An expired Free
> database is inaccessible unless you upgrade it to a paid compute plan. After
> the grace period [of 14 days], Render *deletes* the database (along with all
> of its data).
> — <https://render.com/docs/free#30-day-limit>

So if your database expired **less than 14 days ago**, it still exists. Upgrading
it to the cheapest paid plan (`0.1c-256mb`, **$6/month**) brings it back with
**every row intact**, including everything written after Sep 25 — which the
restore in this guide cannot give you.

1. Open <https://dashboard.render.com> → **Databases**. Does the old database
   still appear?
2. Check the email address on your Render account for a message about your
   database expiring, and one about the grace period ending. The dates tell you
   how much of the 14 days is left.
3. If it is still there and within the grace window: open the database → **Plan**
   → **Compute** → **Edit** → choose `0.1c-256mb` → **Save**. Wait a few minutes,
   then redeploy the web service and confirm the site works.

If you recover the original database, **stop here** — you do not need the rest of
this guide. Ask me and I can copy the recovered data down to your PC or into a
free database so you can drop the paid plan.

If the database is gone from the dashboard, or the grace period has passed, the
data is unrecoverable and you should continue with Step 1.

## Step 1 — Create the new database

1. Go to <https://dashboard.render.com> → **New +** → **Postgres**.
2. Name it e.g. `mental-health-db`, pick the **Free** plan, and choose the
   **same region** as your web service. Regions are Oregon, Ohio, Virginia,
   Frankfurt and Singapore. This matters: a private (internal) connection only
   works between services in the same region. Check your web service's region at
   the top of its page in the dashboard.
3. Click **Create Database** and wait until its status is **Available**.

> **Only one free Postgres is allowed per workspace.** If Render refuses to let
> you create a new free one, the expired instance is still holding your one free
> slot. You must delete the expired database first (database page → scroll to the
> bottom → **Delete Database**), which permanently destroys whatever was left in
> it. Only do that once you have accepted the data loss from Step 0.

> **Set a calendar reminder for day 28.** This free instance will expire too, and
> when it does you will get this exact error again. When that happens, either
> create another one and re-run this guide, or move to a provider whose free tier
> does not expire (Neon and Supabase are the usual choices). Moving later is just
> a matter of changing the environment variables below — nothing else in this
> guide changes.

## Step 2 — Point the web service at it

Open the new database in the Render dashboard and copy the **Internal Database
URL**. Then open your **web service** → **Environment** and either:

**Option A — one variable (simplest).** Add

```
DB_URL=<paste the Internal Database URL>
DB_CONNECTION=pgsql
```

**Option B — individual variables.** Update these to the values shown on the
database page:

```
DB_CONNECTION=pgsql
DB_HOST=<internal hostname, e.g. dpg-xxxx-a>
DB_PORT=5432
DB_DATABASE=<database name>
DB_USERNAME=<user>
DB_PASSWORD=<password>
DB_SSLMODE=prefer
```

While you are here, also set:

```
APP_DEBUG=false
MAIL_PORT=2525
```

**Why `APP_DEBUG=false`:** your screenshot shows the full Ignition stack trace to
the public internet, including the database hostname. With `APP_DEBUG=false` a
database outage shows a plain error page instead of leaking that.

**Why `MAIL_PORT=2525`:** Render free web services cannot send outbound traffic on
ports 25, 465 or 587 — and your configuration uses Brevo's SMTP relay on **587**.
That means verification and OTP emails are silently failing on Render today. Brevo
supports port **2525** for exactly this case, and it is not blocked. If you have
`MAIL_SCHEME=smtps` set, remove it — `smtps` means implicit TLS, which port 2525
does not use.

Delete any old `DB_*` variables left over from the expired instance — in
particular the stale `DB_HOST=dpg-dagkqb0473hc73bhd4d0-a`.

## Step 3 — Redeploy

**Manual Deploy → Deploy latest commit.** `docker/run.sh` runs
`php artisan migrate --force` on every boot, so the new database gets the full
schema automatically. Watch the deploy log for `Running migrations` and confirm
it finishes with no errors.

## Step 4 — Copy the data across

Run this **on your PC**, after step 3 has finished. It uses the database's
**External** Database URL (not the internal one — the internal hostname only
resolves from inside Render).

Render always encrypts external connections and **rejects `sslmode=disable`**, so
keep `?sslmode=require` on the URL (that is the script's default). Note also that
free web services have no Shell access in the dashboard, which is exactly why the
data has to be copied from your machine rather than from inside Render.

1. Make sure the local MySQL database is running (XAMPP → MySQL → Start).

2. In PowerShell, from the project folder:

```powershell
cd C:\Users\Aquinom\Desktop\backtorenz\backtorenz

# Look before you leap: reports what would be copied, changes nothing.
C:\xampp\php\php.exe -d extension=pdo_pgsql tools/restore-mysql-to-postgres.php `
  --pg-url="postgresql://USER:PASSWORD@EXTERNAL-HOST/DBNAME?sslmode=require" `
  --dry-run
```

3. Check the row counts look right, then run it for real by dropping `--dry-run`:

```powershell
C:\xampp\php\php.exe -d extension=pdo_pgsql tools/restore-mysql-to-postgres.php `
  --pg-url="postgresql://USER:PASSWORD@EXTERNAL-HOST/DBNAME?sslmode=require"
```

`-d extension=pdo_pgsql` loads the PostgreSQL driver for this one command. Your
XAMPP `php.ini` already has the DLL, it is just commented out — you can
permanently enable it by uncommenting `;extension=pdo_pgsql` in
`C:\xampp\php\php.ini`, after which you can drop the `-d` flag.

### What the script does

* Reads the target schema **from PostgreSQL**, so it adapts to the current
  migrations rather than assuming the old MySQL shape.
* Copies only the columns that exist on both sides. Columns added by newer
  migrations keep their defaults.
* Converts MySQL `0`/`1` into real PostgreSQL booleans and drops MySQL zero-dates.
* Inserts parents before children based on the foreign keys, all in a single
  transaction — if anything fails, nothing is written.
* Realigns every id sequence to `MAX(id)`, so the next insert does not collide
  with a restored primary key.
* Skips tables Laravel manages itself (`migrations`, `cache`, `sessions`,
  `jobs`, `failed_jobs`, `login_attempts`, `otp_tokens`). Copying `migrations`
  would make Laravel re-run migrations that already ran.

Useful flags: `--dry-run`, `--only=users,appointments`, `--skip=...`,
`--no-truncate`, `--help`.

## Step 5 — Confirm

1. Open <https://mental-health-portal-vd83.onrender.com> and log in.
2. Check a dashboard, an assessment result and a call history.

---

## Troubleshooting

### `FATAL: No SNI information found`

Render rejects external connections that don't send TLS SNI, and **libpq only
started sending SNI in version 12**. XAMPP ships libpq **11.4**, so out of the box
its PHP can never connect to a Render external URL — no amount of `sslmode`
tweaking will help.

Check the version:

```powershell
(Get-Item C:\xampp\php\libpq.dll).VersionInfo.FileVersion
```

If it is below 12, replace `C:\xampp\php\libpq.dll` with a modern one (16.x works)
and copy in the dependency DLLs it needs, which XAMPP lacks:

```
libpq.dll  libiconv-2.dll  libintl-9.dll  libwinpthread-1.dll  libzstd.dll  liblz4.dll
```

Back up the original first. Only PHP's PostgreSQL extensions load `libpq.dll`, so
nothing else in XAMPP is affected. This is already done on this machine, with the
original kept at `C:\xampp\php\libpq.dll.libpq11.4.bak`.

### `could not translate host name ... to address`

You are using the **internal** URL from outside Render. Internal hostnames only
resolve from services inside Render. Use the **External** Database URL.

### `SCHEMA IS EMPTY - migrations have not run yet`

The web service has not deployed since you pointed it at the new database. Deploy
it first — `docker/run.sh` runs `php artisan migrate --force` on boot.

## Notes

* **Passwords still work.** The `users` table is copied verbatim, including the
  bcrypt hashes, so existing logins are unchanged.
* **Expect a data gap.** The newest record in the local database is from
  Sep 24, 2026. Anything created on the live site after that is gone.
* **Legacy tables.** `student_profiles` and `counselor_profiles` exist in MySQL
  but not in PostgreSQL. The script reports them and skips them; nothing in
  `app/`, `routes/` or `database/` references them.
* **Re-running is safe.** The script truncates and refills the target tables
  inside one transaction, so it can be run again after a fresh restore.

## Verification performed

This procedure was tested end-to-end against a throwaway PostgreSQL 16 instance
before being written down:

* All 39 migrations applied cleanly to PostgreSQL.
* All **4507 rows across 19 tables** were copied and then compared column by
  column against MySQL — **zero differences**.
* The application was booted against the restored database: the login,
  registration and landing pages all returned HTTP 200, and Eloquent reads
  (enum casts, booleans, timestamps, relations, multibyte text such as
  `✅ Counselor Accepted Your Call`) all returned correct data.
* Inserts after the restore produced correct new ids in `users`,
  `notifications`, `call_messages` and `emergency_calls`, confirming the
  sequences were realigned.
