<?
/**
 * Marker interface with only one method, which returns administrable directory
 * @author nevil
 */
interface Administrable {

    function getAdministeredDir($subDir='', $relativePath=true);
}
?>
