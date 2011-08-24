<?
/**
 * Implementation of image service admin functionality
 *
 * @author nevil
 */
class admin_imageServiceImpl extends CommonService implements admin_imageService {
    
    public function deleteImg($imgDir, $img) {
        $this->fileUtils->removeFile($img, $imgDir);
    }
    public function insertImg($imgDir, $imgCandidate) {
        $this->fileUtils->saveFile($imgDir, $imgCandidate);
    }
    public function updateImg($imgDir, $currentImg, $imgCandidate) {
        $this->fileUtils->saveFileAs($currentImg, $imgDir, $imgCandidate);
    }
}
?>
