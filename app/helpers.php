<?php
 function uploadFiles($profile_img,$prev_file = '') {
    $img_extension = $profile_img->extension();
    $img_name = date('Ymdhis').''.uniqid().'.'.$img_extension;
    if ($prev_file) {
        $prev_img_file = public_path('uploads/').$prev_file;
        if(file_exists($prev_img_file)) {
            unlink($prev_img_file);
        }
    }
    $profile_img->move(public_path('uploads'),$img_name);
    return $img_name;

}
?>