<?php
if(!$wpdb)
{
    @require('../../../wp-config.php');
}
if(!$wpdb)
{
    echo 'WP Database not available. Quitting';
    die;
}
global $wpdb;
include 'tagthis_core.php';
if(!current_user_can('manage_tagthis'))
    die('Access Denied');
die('the auto updating system has been temporarily disabled. Please upgrade manually till the stable release is on wordpress codex. Sorry for the inconvenience');
/*require('framework.php');
$url=get_option("siteurl")."/wp-admin/admin.php"."?page=tagthis/update_frontend.php";
//using framework for a plugin
//create a new updater object
$updater=new ocframework();
//set the versionpath, pointing to a remote textfile containing only the latest version
$updater->versionpath='http://downloads.oinam.com/wordpress/plugins/tagthis.txt';
//note that this text file can also be generated by a html file or php file but it must have only THE VERSION and nothing else. no other output. The framework copmares the text in the file and the version string
//set the current version of the plugin. Be sure to update the version when you update the plugin
$updater->version='0.9.1';
//set the path of the zip file to be downloaded
$updater->downloadpath='http://downloads.oinam.com/wordpress/plugins/tagthis.zip';
if($_POST["do"])
{
    $updater->update();
}
$updateavailable=$updater->checkForUpdate();

?>
<div class="wrap">
<h2>Update Tagthis</h2>
Use this page to update TagThis. It will download, unzip and update the plugin automatically. You may need to reload your browser page to see the changes.
<br /><br />Current Update Status:<br />
<?php if(!$updateavailable)
echo "Update Not available";
else
{
echo "Update available.";
?>
<form action="<?php echo $url ?>" method="post" >
<input type="hidden" name="do" value=1>
<input type="submit" name="update2" value="UPDATE">
</form>
<?php
}
      */ ?>
