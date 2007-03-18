<?php
/**
 * This is the template that displays the archive directory for a blog
 *
 * This file is not meant to be called directly.
 * It is meant to be called by an include in the main.page.php template.
 * To display the archive directory, you should call a stub AND pass the right parameters
 * For example: /blogs/index.php?disp=arcdir
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2006 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * @package evoskins
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

$archive_mode = $Blog->get_setting('archive_mode');

if( $archive_mode != 'postbypost' )
{	// Do the default display:

	// Call the Archives plugin WITH NO LIMIT & NO MORE LINK:
	$Plugins->call_by_code( 'evo_Arch', array( 'title'=>'',
		                                          'block_start'=>'',
		                                          'block_end'=>'',
		                                          'limit'=>'',
		                                          'more_link'=>'' ) );
	return;
}

// fp>SUSPECT
// Display photos:
// PROOF OF CONCEPT. VERY EXERIMENTAL. VERY NOT RELEASABLE.
// TODO: permissions, statuses, aggregations...


$FileCache = & get_Cache( 'FileCache' );

$FileList = & new DataObjectList2( $FileCache );


$SQL = & new SQL();
$SQL->SELECT( 'post_ID, file_ID, file_title, file_root_type, file_root_ID, file_path, file_alt, file_desc' );
$SQL->FROM( 'T_categories INNER JOIN T_postcats ON cat_ID = postcat_cat_ID
							INNER JOIN T_posts ON postcat_post_ID = post_ID
							INNER JOIN T_links ON post_ID = link_itm_ID
							INNER JOIN T_files ON link_file_ID = file_ID' );
$SQL->WHERE( 'cat_blog_ID = '.$Blog->ID ); // fp> TODO: want to restrict on images :]
$SQL->GROUP_BY( 'link_ID' );
$SQL->ORDER_BY( 'post_'.$Blog->get_setting('orderby').' '.$Blog->get_setting('orderdir')
								.', post_ID '.$Blog->get_setting('orderdir').', link_ID' );

$FileList->sql = $SQL->get();

$FileList->query( false, false, false );

echo '<table class="image_index" cellspacing="3">';

$nb_cols = 8;
$count = 0;
$prev_post_ID = 0;
while( $File = & $FileList->get_next() )
{
	if( ! $File->is_image() )
	{	// Skip anything that is not an image
		// fp> TODO: maybe this property should be stored in link_ltype_ID
		continue;
	}

	if( $count % $nb_cols == 0 )
	{
		echo '<tr>';
	}
	echo '<td>';

	$post_ID = $FileList->rows[$FileList->current_idx-1]->post_ID;
	if( $post_ID != $prev_post_ID )
	{
		$prev_post_ID = $post_ID;
		$count++;
	}

	// Hack a dirty permalink( will redirect to canonical):
	// $link = url_add_param( $Blog->get('url'), 'p='.$post_ID );

	// Hack a link to the right "page". Very daring!!
	$link = url_add_param( $Blog->get('url'), 'paged='.$count );

	echo '<a href="'.$link.'">';
	// Generate the IMG THUMBNAIL tag with all the alt, title and desc if available
	echo '<img src="'.$File->get_thumb_url().'" '
				.'alt="'.$File->dget('alt', 'htmlattr').'" '
				.'title="'.$File->dget('title', 'htmlattr').'" />';
	echo '</a>';

	// fp> TODO: CLEAN link. I really need an ItemListLight now :#
	/*
		WARNING: the above is not an ItemList, it's a FileList :/

		Basic spec for ItemListLight:
		- either params to ItemList2 or parent class. I'm not sure.
		- no loading/nor querying of the content
		- load the title
		- handle the statuses / visibility as well as aggregation
		- basically all we need is be able to generate permalinks
		- we need this for regular text postbypost archives too (dirty links so far)
		- we could use this for any kind of lists (last posts, most popular, sitemap )
	*/

	echo '</td>';
	if( $count % $nb_cols == 0 )
	{
		echo '</tr>';
	}

}

if( $count && ( $count % $nb_cols != 0 ) )
{
	echo '</tr>';
}


echo '</table>';

/*
 * $Log$
 * Revision 1.8  2007/03/18 01:39:57  fplanque
 * renamed _main.php to main.page.php to comply with 2.0 naming scheme.
 * (more to come)
 *
 * Revision 1.7  2007/03/11 20:39:44  fplanque
 * little fix
 *
 * Revision 1.6  2007/01/23 09:25:39  fplanque
 * Configurable sort order.
 *
 * Revision 1.5  2007/01/23 03:46:24  fplanque
 * cleaned up presentation
 *
 * Revision 1.4  2007/01/15 20:48:19  fplanque
 * constrained photoblog image size
 * TODO: sharpness issue
 *
 * Revision 1.3  2006/12/14 23:02:28  fplanque
 * the unbelievable hack :P
 *
 * Revision 1.1  2006/12/14 22:29:37  fplanque
 * thumbnail archives proof of concept
 *
 */
?>