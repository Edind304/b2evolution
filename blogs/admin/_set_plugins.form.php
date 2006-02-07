<?php
/**
 * This file implements the UI view for the plugin settings.
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2005 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * @package admin
 * @todo link to help urls
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );
?>
<fieldset class="clear"><!-- "clear" to fix Konqueror (http://bugs.kde.org/show_bug.cgi?id=117509) -->
	<legend><?php echo T_('Installed plug-ins') ?></legend>
	<table class="grouped" cellspacing="0">
		<thead>
		<tr>
			<th class="firstcol"><?php echo T_('Plug-in') ?></th>
			<th><?php echo T_('Priority') ?></th>
			<th title="<?php echo T_('When should rendering apply?') ?>"><?php echo T_('Apply') ?></th>
			<th class="advanced_info" title="<?php echo T_('The code to call the plugin by code (SkinTag) or as Renderer.') ?>"><?php echo /* TRANS: Code of a plugin */ T_('Code') ?></th>
			<th><?php echo T_('Description') ?></th>
			<?php
			if( $current_User->check_perm( 'options', 'edit', false ) )
			{ ?>
				<th class="lastcol"><?php echo T_('Actions') ?></th>
				<?php
			} ?>
			<th><?php echo T_('Help') ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		$apply_rendering_values = $Plugins->get_apply_rendering_values(true); // with descs
		$Plugins->restart();	 // make sure iterator is at start position
		$count = 0;
		while( $loop_Plugin = & $Plugins->get_next() )
		{
		?>
		<tr<?php if( $count++ % 2 ) { echo ' class="odd"'; } ?>>
			<td class="firstcol">
				<a href="plugins.php?action=edit_settings&amp;plugin_ID=<?php echo $loop_Plugin->ID ?>" title="<?php echo T_('Edit plugin settings!') ?>">
				<?php	$loop_Plugin->name(); ?>
				</a>
			</td>
			<td class="right"><?php	echo $loop_Plugin->priority; ?></td>
			<td><span title="<?php echo format_to_output( $apply_rendering_values[$loop_Plugin->apply_rendering], 'htmlattr' ) ?>"><?php echo $loop_Plugin->apply_rendering; ?></span></td>
			<td>
				<?php $loop_Plugin->code() ?>
			</td>
			<td>
				<?php $loop_Plugin->short_desc(); ?>
			</td>
			<?php
			if( $current_User->check_perm( 'options', 'edit', false ) )
			{ ?>
				<td class="lastcol right">
					<a href="plugins.php?action=edit_settings&amp;plugin_ID=<?php echo $loop_Plugin->ID ?>" title="<?php echo T_('Edit plugin settings!') ?>"><?php echo get_icon( 'edit', 'imgtag', array( 'title' => T_('Edit plugin settings!') ) ) ?></a>
					<a href="plugins.php?action=uninstall&amp;plugin_ID=<?php echo $loop_Plugin->ID ?>" title="<?php echo T_('Un-install this plugin!') ?>"><?php echo get_icon( 'delete', 'imgtag', array( 'title' => T_('Un-install this plugin!') ) ) ?></a>
				</td>
				<?php
			} ?>
			<td>
				[<a title="<?php echo T_('Display info') ?>" href="plugins.php?action=info&amp;plugin_ID=<?php echo $loop_Plugin->ID ?>">?</a>]
				<?php
				// Help icons, if available:
				$help_icons = array();
				if( $help_external = $loop_Plugin->get_help_icon( NULL, NULL, true ) )
				{
					$help_icons[] = $help_external;
				}
				if( $help_internal = $loop_Plugin->get_help_icon() )
				{
					$help_icons[] = $help_internal;
				}
				if( ! empty($help_icons) )
				{
					echo implode( ' ', $help_icons );
				}
				?>
			</td>
		</tr>
		<?php
		}
		?>
		</tbody>
	</table>
	<p class="center">
		<a href="<?php echo $pagenow ?>?action=reload_plugins"><?php echo T_('Reload events and codes for installed plug-ins.')
		/* TODO: explain why we need this and find a better name. ONE THING SEEMS SURE THOUGH: this does NOT "reload" the plugins. */ ?></a>
	</p>
</fieldset>


<fieldset>
	<legend><?php echo T_('Available plug-ins') ?></legend>
	<table class="grouped" cellspacing="0">
		<tbody>
		<tr>
			<th class="firstcol"><?php echo T_('Plug-in') ?></th>
			<th><?php echo T_('Actions') ?></th>
			<th><?php echo T_('Help') ?></th>
			<th class="lastcol"><?php echo T_('Description') ?></th>
		</tr>
		<?php
		$AvailablePlugins->restart();	 // make sure iterator is at start position
		$count = 0;
		while( $loop_Plugin = & $AvailablePlugins->get_next() )
		{
		?>
		<tr<?php if( $count++ % 2 ) { echo ' class="odd"'; } ?>>
			<td class="firstcol">
				<?php $loop_Plugin->name(); ?>
			</td>
			<td>
				<?php
				$registrations = $Plugins->count_regs($loop_Plugin->classname);

				if( $current_User->check_perm( 'options', 'edit', false )
				    && ( ! isset( $loop_Plugin->nr_of_installs )
				         || $registrations < $loop_Plugin->nr_of_installs ) )
				{ // number of installations are not limited or not reached yet and user has "edit options" perms
					?>
					[<a href="plugins.php?action=install&amp;plugin=<?php echo rawurlencode($loop_Plugin->classname) ?>"><?php
						echo T_('Install');
						if( $registrations )
						{	// This plugin is already installed
							echo ' #'.($registrations+1);
						}
						?></a>]
					<?php
				}
				?>
			</td>
			<td>
				[<a title="<?php echo T_('Display info') ?>" href="plugins.php?action=info&amp;plugin_ID=<?php echo $loop_Plugin->ID ?>">?</a>]
				<?php
				// Help icons, if available:
				$help_icons = array();
				if( $help_external = $loop_Plugin->get_help_icon( NULL, NULL, true ) )
				{
					$help_icons[] = $help_external;
				}
				if( $help_internal = $loop_Plugin->get_help_icon() )
				{
					$help_icons[] = $help_internal;
				}
				if( ! empty($help_icons) )
				{
					echo implode( ' ', $help_icons );
				}
				?>
			</td>
			<td class="lastcol">
				<?php
				$loop_Plugin->short_desc();
				/*
				// Available events:
				$registered_events = implode( ', ', $AvailablePlugins->get_registered_events( $loop_Plugin ) );
				if( empty($registered_events) )
				{
					$registered_events = '-';
				}
				echo '<span class="advanced_info notes"><br />'.T_('Registered events:').' '.$registered_events.'</span>';
				*/
				?>
			</td>
		</tr>
		<?php
		flush();
		}
		?>
		</tbody>
	</table>
</fieldset>