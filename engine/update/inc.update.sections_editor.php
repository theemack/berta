<?php

$property = $decoded['property'];

if($property == 'title') {  // section title
    throw new Exception('Deprecated branch of code called for section editor function: Edit title!');
}
else if($property == 'type') {  // section external link
    throw new Exception('Deprecated branch of code called for section editor function: Edit type!');
}
else if($property == 'published') { // attributes
    throw new Exception('Deprecated branch of code called for section editor function: Edit published!');
}
else if($property == 'galleryOrder') {
    throw new Exception('Deprecated branch of code called for section editor function: Background gallery order!');
}
else if($property == 'galleryImageDelete') {
    throw new Exception('Deprecated branch of code called for section editor function: Background gallery delete!');
}
else if($decoded['property'] == 'galleryImageCaption') {    // image / video caption
    throw new Exception('Deprecated branch of code called for section editor function: Background gallery image caption!');
}
else if($decoded['action'] == 'SET_AUTOPLAY') {
    throw new Exception('Deprecated branch of code called for section editor function: Background gallery autoplay!');
}
else if($decoded['action'] == 'SET_BG_IMG_SIZE') {
    throw new Exception('Deprecated branch of code called for section editor function: Background gallery image size!');
}
else if($decoded['action'] == 'SET_BG_CAPTION_COLOR') {
    throw new Exception('Deprecated branch of code called for section editor function: Background gallery image caption color!');
}
else if($decoded['action'] == 'SET_BG_CAPTION_BACK_COLOR') {
    throw new Exception('Deprecated branch of code called for section editor function: Background gallery image caption background color!');
}
else if($decoded['action'] == 'SET_BG_NAVIGATION') {
    throw new Exception('Deprecated branch of code called for section editor function: Background gallery navigation!');
}
else if($decoded['action'] == 'SET_BG_ANIMATION') {
    throw new Exception('Deprecated branch of code called for section editor function: Background gallery animation!');
}
else if($decoded['action'] == 'SET_BG_FADE_CONTENT') {
    throw new Exception('Deprecated branch of code called for section editor function: Background gallery fade content!');
}
else if($decoded['action'] == 'RESET_BG_CAPTION_COLOR') {
    $sectionsList = BertaEditor::getSections();
    $sName = $decoded['section'];
    if(empty($sectionsList[$sName]['mediaCacheData']['@attributes'])) $sectionsList[$sName]['mediaCacheData']['@attributes'] = array();
    $sectionsList[$sName]['mediaCacheData']['@attributes']['caption_color'] = '';
    BertaEditor::saveSections($sectionsList);
}
else if($decoded['action'] == 'RESET_BG_CAPTION_BACK_COLOR') {
    $sectionsList = BertaEditor::getSections();
    $sName = $decoded['section'];
    if(empty($sectionsList[$sName]['mediaCacheData']['@attributes'])) $sectionsList[$sName]['mediaCacheData']['@attributes'] = array();
    $sectionsList[$sName]['mediaCacheData']['@attributes']['caption_bg_color'] = '';
    BertaEditor::saveSections($sectionsList);
}
else if($decoded['action'] == 'sectionBgColorReset') {
    $sectionsList = BertaEditor::getSections();
    $sName = $decoded['section'];
    if(isset($sectionsList[$sName]['sectionBgColor'])) unset($sectionsList[$sName]['sectionBgColor']);
    BertaEditor::saveSections($sectionsList);
}
else if($decoded['action'] == 'ORDER_SECTIONS') {   // apply the new order
    throw new Exception('Deprecated branch of code called for section editor function: Order sections!');
}
else if($decoded['action'] == 'CREATE_NEW_SECTION') {
    throw new Exception('Deprecated branch of code called for section editor function: Crete section!');
}
else if($decoded['action'] == 'DELETE_SECTION') {   // delete a section
    throw new Exception('Deprecated branch of code called for section editor function: Delete section!');
}
else {
    throw new Exception('Deprecated branch of code called for section editor function: Template-specific property update!');
}

?>
