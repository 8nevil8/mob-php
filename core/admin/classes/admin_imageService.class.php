<?
/**
 * Interface for images adminstration.
 *
 * @author nevil
 */
interface admin_imageService {

    /**
     * Delete specified image from $imgDir
     */
    function deleteImg($imgDir, $img);

    /**
     * Updates current image. Candidate img is taken from _FILES[$imgCandidate] array
     */
    function updateImg($imgDir, $currentImg, $imgCandidate);

    /**
     * Inserts image to imgDir. Img is taken from _FILES[$imgCandidate] array
     */
    function insertImg($imgDir, $imgCandidate);
}
?>
