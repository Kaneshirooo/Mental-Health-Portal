<?php
/**
 * Test clean standard DOCX package builder
 */

function generateStandardDocx($outputPath, $sections, $manualDir) {
    $zip = new ZipArchive();
    if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new Exception("Cannot create $outputPath");
    }

    // 1. [Content_Types].xml
    $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
        . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' . "\n"
        . '  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' . "\n"
        . '  <Default Extension="xml" ContentType="application/xml"/>' . "\n"
        . '  <Default Extension="png" ContentType="image/png"/>' . "\n"
        . '  <Default Extension="jpg" ContentType="image/jpeg"/>' . "\n"
        . '  <Default Extension="jpeg" ContentType="image/jpeg"/>' . "\n"
        . '  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>' . "\n"
        . '  <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>' . "\n"
        . '  <Override PartName="/word/settings.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml"/>' . "\n"
        . '  <Override PartName="/word/fontTable.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.fontTable+xml"/>' . "\n"
        . '  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>' . "\n"
        . '  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>' . "\n"
        . '</Types>';
    $zip->addFromString('[Content_Types].xml', $contentTypes);

    // 2. _rels/.rels
    $globalRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n"
        . '  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>' . "\n"
        . '  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>' . "\n"
        . '  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>' . "\n"
        . '</Relationships>';
    $zip->addFromString('_rels/.rels', $globalRels);

    // 3. docProps/core.xml
    $now = gmdate('Y-m-d\TH:i:s\Z');
    $coreProps = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
        . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . "\n"
        . '  <dc:title>PSU Mental Health Portal - User Manual</dc:title>' . "\n"
        . '  <dc:creator>Pangasinan State University</dc:creator>' . "\n"
        . '  <cp:lastModifiedBy>Pangasinan State University</cp:lastModifiedBy>' . "\n"
        . '  <dcterms:created xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:created>' . "\n"
        . '  <dcterms:modified xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:modified>' . "\n"
        . '</cp:coreProperties>';
    $zip->addFromString('docProps/core.xml', $coreProps);

    // 4. docProps/app.xml
    $appProps = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
        . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">' . "\n"
        . '  <Application>Microsoft Word</Application>' . "\n"
        . '  <DocSecurity>0</DocSecurity>' . "\n"
        . '  <Lines>' . count($sections) * 10 . '</Lines>' . "\n"
        . '  <Paragraphs>' . count($sections) * 8 . '</Paragraphs>' . "\n"
        . '  <Company>Pangasinan State University</Company>' . "\n"
        . '</Properties>';
    $zip->addFromString('docProps/app.xml', $appProps);

    // 5. word/settings.xml
    $settings = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
        . '<w:settings xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">' . "\n"
        . '  <w:zoom w:percent="100"/>' . "\n"
        . '  <w:defaultTabStop w:val="720"/>' . "\n"
        . '  <w:characterSpacingControl w:val="doNotCompress"/>' . "\n"
        . '</w:settings>';
    $zip->addFromString('word/settings.xml', $settings);

    // 6. word/fontTable.xml
    $fontTable = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
        . '<w:fontTable xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">' . "\n"
        . '  <w:font w:name="Times New Roman"><w:panose1 w:val="02020603050405020304"/><w:charset w:val="00"/><w:family w:val="roman"/><w:pitch w:val="variable"/></w:font>' . "\n"
        . '  <w:font w:name="Arial"><w:panose1 w:val="020B0604020202020204"/><w:charset w:val="00"/><w:family w:val="swiss"/><w:pitch w:val="variable"/></w:font>' . "\n"
        . '  <w:font w:name="Calibri"><w:panose1 w:val="020F0502020204030204"/><w:charset w:val="00"/><w:family w:val="swiss"/><w:pitch w:val="variable"/></w:font>' . "\n"
        . '</w:fontTable>';
    $zip->addFromString('word/fontTable.xml', $fontTable);

    // 7. word/styles.xml
    $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
        . '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">' . "\n"
        . '  <w:docDefaults>' . "\n"
        . '    <w:rPrDefault><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:sz w:val="24"/><w:szCs w:val="24"/><w:lang w:val="en-US"/></w:rPr></w:rPrDefault>' . "\n"
        . '    <w:pPrDefault><w:pPr><w:spacing w:line="276" w:lineRule="auto"/></w:pPr></w:pPrDefault>' . "\n"
        . '  </w:docDefaults>' . "\n"
        . '  <w:style w:type="paragraph" w:default="1" w:styleId="Normal">' . "\n"
        . '    <w:name w:val="Normal"/>' . "\n"
        . '    <w:qFormat/>' . "\n"
        . '  </w:style>' . "\n"
        . '  <w:style w:type="paragraph" w:styleId="Heading1">' . "\n"
        . '    <w:name w:val="heading 1"/>' . "\n"
        . '    <w:basedOn w:val="Normal"/>' . "\n"
        . '    <w:next w:val="Normal"/>' . "\n"
        . '    <w:qFormat/>' . "\n"
        . '    <w:pPr><w:keepNext/><w:spacing w:before="480" w:after="240"/><w:jc w:val="center"/></w:pPr>' . "\n"
        . '    <w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:b/><w:sz w:val="40"/><w:szCs w:val="40"/><w:color w:val="0F2C59"/></w:rPr>' . "\n"
        . '  </w:style>' . "\n"
        . '  <w:style w:type="paragraph" w:styleId="Heading2">' . "\n"
        . '    <w:name w:val="heading 2"/>' . "\n"
        . '    <w:basedOn w:val="Normal"/>' . "\n"
        . '    <w:next w:val="Normal"/>' . "\n"
        . '    <w:qFormat/>' . "\n"
        . '    <w:pPr><w:keepNext/><w:spacing w:before="280" w:after="120"/><w:jc w:val="left"/></w:pPr>' . "\n"
        . '    <w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:b/><w:sz w:val="30"/><w:szCs w:val="30"/><w:color w:val="0F2C59"/></w:rPr>' . "\n"
        . '  </w:style>' . "\n"
        . '</w:styles>';
    $zip->addFromString('word/styles.xml', $styles);

    // 8. Process images & build document.xml.rels
    $docRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n"
        . '  <Relationship Id="rIdStyles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' . "\n"
        . '  <Relationship Id="rIdSettings" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/settings" Target="settings.xml"/>' . "\n"
        . '  <Relationship Id="rIdFontTable" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/fontTable" Target="fontTable.xml"/>' . "\n";

    $imgCounter = 1;
    $sectionImgRels = [];
    foreach ($sections as $idx => $s) {
        if (!empty($s['image']) && file_exists($s['image']['file'])) {
            $ext = strtolower(pathinfo($s['image']['file'], PATHINFO_EXTENSION));
            if ($ext === 'jpeg') $ext = 'jpg';
            $mediaName = "media/image{$imgCounter}.{$ext}";
            $zip->addFile($s['image']['file'], "word/{$mediaName}");
            
            $rId = "rIdImg{$imgCounter}";
            $docRels .= "  <Relationship Id=\"{$rId}\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/image\" Target=\"{$mediaName}\"/>\n";
            
            $sectionImgRels[$idx] = [
                'rId' => $rId,
                'name' => "image{$imgCounter}.{$ext}",
                'caption' => $s['image']['caption'],
                'ext' => $ext,
                'file' => $s['image']['file']
            ];
            $imgCounter++;
        }
    }
    $docRels .= '</Relationships>';
    $zip->addFromString('word/_rels/document.xml.rels', $docRels);

    // 9. word/document.xml
    $docXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
        . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">' . "\n"
        . '<w:body>' . "\n";

    // Title Page
    $docXml .= '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:before="720" w:after="160"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="52"/><w:szCs w:val="52"/><w:color w:val="0F2C59"/></w:rPr><w:t>USER MANUAL</w:t></w:r></w:p>' . "\n";
    $docXml .= '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:before="0" w:after="240"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="34"/><w:szCs w:val="34"/><w:color w:val="1E293B"/></w:rPr><w:t>PSU MENTAL HEALTH PORTAL: AN INTEGRATED DIGITAL WELLNESS AND COUNSELING MANAGEMENT SYSTEM</w:t></w:r></w:p>' . "\n";
    $docXml .= '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:before="0" w:after="720"/></w:pPr><w:r><w:rPr><w:i/><w:sz w:val="24"/><w:szCs w:val="24"/><w:color w:val="64748B"/></w:rPr><w:t>Pangasinan State University - Lingayen Campus | Compassionate Care Every Step of the Way</w:t></w:r></w:p>' . "\n";

    $currentPart = '';
    foreach ($sections as $idx => $s) {
        if ($s['part'] !== $currentPart) {
            $currentPart = $s['part'];
            // Page break before parts (except first)
            if ($idx > 0) {
                $docXml .= '<w:p><w:r><w:br w:type="page"/></w:r></w:p>' . "\n";
            }
            $docXml .= '<w:p><w:pPr><w:pStyle w:val="Heading1"/><w:jc w:val="center"/><w:spacing w:before="480" w:after="240"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="38"/><w:szCs w:val="38"/><w:color w:val="0F2C59"/></w:rPr><w:t>' . htmlspecialchars($currentPart) . '</w:t></w:r></w:p>' . "\n";
        }

        // Section Heading
        $docXml .= '<w:p><w:pPr><w:pStyle w:val="Heading2"/><w:spacing w:before="280" w:after="120"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="28"/><w:szCs w:val="28"/><w:color w:val="0F2C59"/></w:rPr><w:t>' . htmlspecialchars("{$s['code']}: {$s['title']}") . '</w:t></w:r></w:p>' . "\n";

        // Section Description
        $docXml .= '<w:p><w:pPr><w:jc w:val="both"/><w:spacing w:before="60" w:after="120"/></w:pPr><w:r><w:rPr><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>' . htmlspecialchars($s['desc']) . '</w:t></w:r></w:p>' . "\n";

        // Steps
        foreach ($s['steps'] as $stepText) {
            $parts = explode(':', $stepText, 2);
            $stepLabel = $parts[0] . ':';
            $stepBody = isset($parts[1]) ? $parts[1] : '';

            $docXml .= '<w:p><w:pPr><w:jc w:val="both"/><w:spacing w:before="60" w:after="60"/><w:ind w:left="360"/></w:pPr>';
            $docXml .= '<w:r><w:rPr><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t xml:space="preserve">' . htmlspecialchars($stepLabel) . ' </w:t></w:r>';
            $docXml .= '<w:r><w:rPr><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>' . htmlspecialchars(ltrim($stepBody)) . '</w:t></w:r>';
            $docXml .= '</w:p>' . "\n";
        }

        // Note
        if (!empty($s['note'])) {
            $docXml .= '<w:p><w:pPr><w:jc w:val="both"/><w:spacing w:before="120" w:after="120"/><w:ind w:left="720" w:right="720"/></w:pPr>';
            $docXml .= '<w:r><w:rPr><w:b/><w:i/><w:sz w:val="22"/><w:szCs w:val="22"/><w:color w:val="0F766E"/></w:rPr><w:t xml:space="preserve">Note: </w:t></w:r>';
            $docXml .= '<w:r><w:rPr><w:i/><w:sz w:val="22"/><w:szCs w:val="22"/><w:color w:val="334155"/></w:rPr><w:t>' . htmlspecialchars($s['note']) . '</w:t></w:r>';
            $docXml .= '</w:p>' . "\n";
        }

        // Image Embedding
        if (isset($sectionImgRels[$idx])) {
            $imgInfo = $sectionImgRels[$idx];
            $size = @getimagesize($imgInfo['file']);
            $origW = ($size && $size[0]) ? $size[0] : 800;
            $origH = ($size && $size[1]) ? $size[1] : 450;
            
            // Standard A4 page width printable: 5.8 inches = 5,303,520 EMUs
            $maxW = 5303520;
            $maxH = 3400000;
            $scale = min($maxW / $origW, $maxH / $origH);
            $cx = (int)round($origW * $scale);
            $cy = (int)round($origH * $scale);

            $docId = 1000 + $idx;

            $docXml .= '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:before="180" w:after="60"/></w:pPr>';
            $docXml .= '<w:r><w:drawing>';
            $docXml .= '<wp:inline distT="0" distB="0" distL="0" distR="0">';
            $docXml .= "<wp:extent cx=\"{$cx}\" cy=\"{$cy}\"/>";
            $docXml .= '<wp:effectExtent l="0" t="0" r="0" b="0"/>';
            $docXml .= "<wp:docPr id=\"{$docId}\" name=\"Figure_{$docId}\"/>";
            $docXml .= '<wp:cNvGraphicFramePr><a:graphicFrameLocks xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" noChangeAspect="true"/></wp:cNvGraphicFramePr>';
            $docXml .= '<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">';
            $docXml .= '<a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">';
            $docXml .= '<pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">';
            $docXml .= '<pic:nvPicPr>';
            $docXml .= "<pic:cNvPr id=\"{$docId}\" name=\"{$imgInfo['name']}\"/>";
            $docXml .= '<pic:cNvPicPr/>';
            $docXml .= '</pic:nvPicPr>';
            $docXml .= '<pic:blipFill>';
            $docXml .= "<a:blip r:embed=\"{$imgInfo['rId']}\"/>";
            $docXml .= '<a:stretch><a:fillRect/></a:stretch>';
            $docXml .= '</pic:blipFill>';
            $docXml .= '<pic:spPr>';
            $docXml .= '<a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm>';
            $docXml .= '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>';
            $docXml .= '</pic:spPr>';
            $docXml .= '</pic:pic>';
            $docXml .= '</a:graphicData>';
            $docXml .= '</a:graphic>';
            $docXml .= '</wp:inline>';
            $docXml .= '</w:drawing></w:r></w:p>' . "\n";

            // Image Caption
            $docXml .= '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:before="60" w:after="240"/></w:pPr>';
            $docXml .= '<w:r><w:rPr><w:i/><w:sz w:val="20"/><w:szCs w:val="20"/><w:color w:val="64748B"/></w:rPr><w:t>' . htmlspecialchars($imgInfo['caption']) . '</w:t></w:r></w:p>' . "\n";
        }
    }

    // A4 Portrait Page (11906 x 16838 twips = standard A4)
    $docXml .= '<w:sectPr>'
        . '<w:pgSz w:w="11906" w:h="16838" w:code="9"/>'
        . '<w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="720" w:footer="720" w:gutter="0"/>'
        . '<w:cols w:space="720"/>'
        . '</w:sectPr>' . "\n";
    $docXml .= '</w:body></w:document>';

    $zip->addFromString('word/document.xml', $docXml);
    $zip->close();
}
