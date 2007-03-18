<?php
/**
 * This template generates an RSS 1.0 (RDF) feed for the requested blog's latest comments
 *
 * See {@link http://web.resource.org/rss/1.0/}
 *
 * @package evoskins
 * @subpackage rdf
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

$CommentList = & new CommentList( $Blog, "'comment'", array('published'), '',	'',	'DESC',	'',	$Blog->get_setting('posts_per_feed') );

skin_content_header( 'application/xml' );	// Sets charset!

echo '<?xml version="1.0" encoding="'.$io_charset.'"?'.'>';
?>
<!-- generator="<?php echo $app_name; ?>/<?php echo $app_version ?>" -->
<rdf:RDF xmlns="http://purl.org/rss/1.0/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:admin="http://webns.net/mvcb/" xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel rdf:about="<?php $Blog->disp( 'blogurl', 'xmlattr' ) ?>">
	<title><?php
		$Blog->disp( 'name', 'xml' );
		request_title( ' - ', '', ' - ', 'xml' );
	?></title>
	<link><?php $Blog->disp( 'lastcommentsurl', 'xml' ) ?></link>
	<description></description>
	<dc:language><?php $Blog->disp( 'locale', 'xml' ) ?></dc:language>
	<admin:generatorAgent rdf:resource="http://b2evolution.net/?v=<?php echo $app_version ?>"/>
	<sy:updatePeriod>hourly</sy:updatePeriod>
	<sy:updateFrequency>1</sy:updateFrequency>
	<sy:updateBase>2000-01-01T12:00+00:00</sy:updateBase>
	<items>
		<rdf:Seq>
		<?php while( $Comment = & $CommentList->get_next() )
		{ // Loop through comments:
			?>
			<rdf:li rdf:resource="<?php $Comment->permanent_url() ?>"/>
			<?php
		} ?>
		</rdf:Seq>
	</items>
</channel>
<?php
$CommentList->restart();
while( $Comment = & $CommentList->get_next() )
{ // Loop through comments:
	// Load comment's Item:
	$Comment->get_Item();
	?>
<item rdf:about="<?php $Comment->permanent_url() ?>">
	<title><?php echo format_to_output( T_('In response to:'), 'xml' ) ?> <?php $Comment->Item->title( '', '', false, 'xml' ) ?></title>
	<link><?php $Comment->permanent_url() ?></link>
	<dc:date><?php $Comment->date( 'isoZ', true ); ?></dc:date>
	<dc:creator><?php $Comment->author( '', '#', '', '#', 'xml' ) ?></dc:creator>
	<description><?php echo make_rel_links_abs( $Comment->get_content('entityencoded') ); ?></description>
	<content:encoded><![CDATA[<?php echo make_rel_links_abs( $Comment->get_content() ); ?>]]></content:encoded>
</item>
<?php } // End of comment loop. ?>
</rdf:RDF>
<?php
	$Hit->log(); // log the hit on this page

	// This is a self contained XML document, make sure there is no additional output:
	exit();
?>