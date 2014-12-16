<?php
/**
 * This is the template that displays the site map (the real one, not the XML thing) for a blog
 *
 * This file is not meant to be called directly.
 * It is meant to be called by an include in the main.page.php template.
 * To display the archive directory, you should call a stub AND pass the right parameters
 * For example: /blogs/index.php?disp=postidx
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2014 by Francois Planque - {@link http://fplanque.com/}
 *
 * @package evoskins
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

global $MainList;

// --------------------------------- START OF COMMON LINKS --------------------------------
skin_widget( array(
		// CODE for the widget:
		'widget' => 'coll_search_form',
		// Optional display params
		'block_start' => '',
		'block_end' => '',
		'block_display_title' => false,
		'disp_search_options' => 0,
		'search_class' => 'extended_search_form',
		'use_search_disp' => 1,
		'button' => T_('Search')
	) );
// ---------------------------------- END OF COMMON LINKS ---------------------------------

// Display the search result
search_result_block( array(
		'title_prefix_post'     => T_('Topic: '),
		'title_prefix_comment'  => T_('Reply: '),
		'title_prefix_category' => T_('Forum: '),
		'title_prefix_tag'      => T_('Tag: '),
		/*'block_start' => '<table class="bForums" width="100%" cellspacing="1" cellpadding="2" border="0">
			<tr>
				<th>'.T_('Title').'</th>
				<th width="60%">'.T_('Content').'</th>
				<th>'.T_('Author').'</th>
				</tr>',
		'block_end'   => '</table>',
		'row_start'   => '<tr>',
		'row_end'     => '</tr>',
		'cell_title_start'   => '<td class="left">',
		'cell_title_end'     => '</td>',
		'cell_author_start'  => '<td class="left">',
		'cell_author_end'    => '</td>',
		'cell_author_empty'  => '&nbsp;',
		'cell_content_start' => '<td class="left row2">',
		'cell_content_end'   => '</td>',*/
	) );

?>