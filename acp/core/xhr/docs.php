<?php

/**
 * global variables
 * @var string $languagePack
 * @var array $lang
 */

if(isset($_GET['file'])) {

    $file = se_filter_filepath($_GET['file']);

    echo '<div class="modal-dialog modal-lg modal-dialog-centered">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">'.$file.'</h5>
    </div>
    <div class="modal-body">
    <div id="showModalContent" hx-get="/admin/docs/read/?show_file='.$file.'" hx-trigger="load">
    </div> 
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'.$lang['close'].'</button>
    </div>
  </div>
</div>';
}

if(isset($_GET['show_file'])) {

    $doc_file = '../acp/docs/'.$languagePack.'/'.se_filter_filepath($_GET['show_file']);
    $Parsedown = new Parsedown();
    $show_file = se_parse_docs_file($doc_file);
    $docs_viewer_content = $show_file['content'];
    echo $show_file['content'];
}