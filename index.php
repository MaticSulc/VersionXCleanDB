<?php
$eventName = $modx->event->name;
$modx->addPackage('versionx', MODX_CORE_PATH.'components/versionx/model/');

function deleteOld($id, $tablename){
    
    $minimumVersions = 2;
    $maximumVersionAgeDays = 180;
    $maximumVersionAgeDaysMs = $maximumVersionAgeDays * 86400;
    
    global $modx; //modx scope fix
    
    $countRows = $modx->getCount($tablename, ['content_id' => $id]);
    if($countRows <= $minimumVersions)  return;
    
    $query = $modx->newQuery($tablename, ['content_id' => $id])->sortby('saved'); //oldest first
    $data = $modx->getCollection($tablename, $query);
    $toDelete = $countRows - $minimumVersions;
    $cnt = 0;
    foreach($data as $el){
        $savedTimestamp = strtotime($el->get('saved'));
        if ((time() - $savedTimestamp) >= $maximumVersionAgeDaysMs){
            $modx->log(xPDO::LOG_LEVEL_ERROR, 'removing old ' . $tablename . ' with time ' . $el->get('saved'));
            $el->remove();
            $el->save();
            $cnt++;
        }
        if($cnt >= $toDelete) break;
    }
}

switch($eventName) {
    case 'OnEmptyTrash':
        $vx_tables = ['vxResource', 'vxChunk', 'vxSnippet', 'vxPlugin', 'vxTemplate', 'vxTemplateVar'];
        foreach($ids as $id){
            foreach($vx_tables as $table){
                 $modx->removeCollection($table, ['content_id' => $id]);
            }
        }
        break;
    case 'OnDocFormSave':
        $id = $resource->get('id');
        deleteOld($id, 'vxResource');
        
    break;
    
    case 'OnPluginSave':
        $id = $plugin->get('id');
        deleteOld($id, 'vxPlugin');
    break;
    
    case 'OnTemplateVarSave':
         $id = $templateVar->get('id');
         deleteOld($id, 'vxTemplateVar');
    break;
    
    case 'OnChunkSave':
         $id = $chunk->get('id');
         deleteOld($id, 'vxChunk');
    break;
    
    case 'OnSnippetSave':
         $id = $snippet->get('id');
         deleteOld($id, 'vxSnippet');
    break;
    
    case 'OnTemplateSave':
         $id = $template->get('id');
         deleteOld($id, 'vxTemplate');
    break;
}
