<?php $this->load->helper('inflector'); ?>

<div class="bttn-container">
	<?php
	if ($this->uri->segment(2) === 'conversation'):
		if ($this->uri->segment(3) === 'folder'):

			// _tni_ added this for translation on the inbox, outbox etc.
			$theFolder = $this->uri->segment(4);
			switch ($theFolder)
			{
				case 'inbox':
					$theFname = tr_raw('Inbox');
					break;
				case 'outbox':
					$theFname = tr_raw('Outbox');
					break;
				case 'sentitems':
					$theFname = tr_raw('Sent items');
					break;
				case 'phonebook':
					$theFname = tr_raw('Phonebook');
					break;
				default:
					$theFname = $this->uri->segment(4);
					break;
			}
			$anchor_url = 'messages/folder/'.$this->uri->segment(4);
			$anchor_text = '&lsaquo;&lsaquo; '.tr('Back to {0}', NULL, $theFname);
		elseif ($this->uri->segment(3) === 'my_folder'):
			$anchor_url = 'messages/my_folder/'.$this->uri->segment(4).'/'.$this->uri->segment(6);
			$anchor_text = '&lsaquo;&lsaquo; '.tr('Back to {0}', NULL, humanize($this->Kalkun_model->get_folders('name', $this->uri->segment(6))->row('name')));
		endif;
	?>
	<div class="bttn-group">
		<div><?php echo anchor($anchor_url, $anchor_text, array('class' => 'button', 'id' => 'back_threadlist'));?></div>
	</div>

	<?php endif;?>
	<div class="bttn-group">
		<div><a href="javascript:void(0);" class="select_all_button button"><?php echo tr('Select all');?></a></div>
		<div><a href="javascript:void(0);" class="clear_all_button button"><?php echo tr('Deselect all');?></a></div>
	</div>

	<div class="bttn-group">
		<?php if ($this->uri->segment(2) === 'conversation' && $this->uri->segment(4) === 'inbox') :
			if ($this->uri->segment(6) !== '6') : ?>
		<div><a href="javascript:void(0);" class="spam_button button"><?php echo tr('Report spam');?></a></div>
		<?php   else : ?>
		<div><a href="javascript:void(0);" class="ham_button button"><?php echo tr('Not spam');?></a></div>
		<?php   endif;
		endif;?>
	</div>

	<div class="bttn-group">
		<?php
	if ($this->uri->segment(2) === 'folder' && $this->uri->segment(3) === 'outbox'):
	elseif ($this->uri->segment(2) === 'conversation' && $this->uri->segment(4) === 'outbox'):
	else:?>

		<?php if ($this->uri->segment(4) === '5' or $this->uri->segment(6) === '5') : ?>
		<div><a href="javascript:void(0);" class="recover_button button"><?php echo tr('Recover');?></a></div>
		<?php endif; ?>
		<div><a class="move_to_button button" href="javascript:void(0);"><?php echo tr('Move to');?></a></div>
		<?php endif; ?>
		<div><a class="global_delete button" href="javascript:void(0);">
				<?php
	if ($this->uri->segment(4) === '5' or $this->uri->segment(6) === '5' or $this->uri->segment(4) === '6' or $this->uri->segment(6) === '6'):
		echo tr('Delete permanently');
	else:
		echo tr('Delete');
	endif;
	?></a></div>
	</div>
	<?php if ($this->uri->segment(2) !== 'search'): ?>

	<div class="bttn-group">
		<div><a href="javascript:void(0);" class="refresh_button button"><?php echo tr('Refresh');?></a></div>
		<?php if (($this->uri->segment(3) === 'inbox') || ($this->uri->segment(4) === 'inbox')): ?>
		<div><a href="javascript:void(0);" class="process_incoming_msgs_button button"><?php echo tr('Process incoming messages');?></a></div>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<div class="bttn-group">
		<?php if ($this->uri->segment(2) === 'conversation' && $this->uri->segment(4) === 'sentitems'): ?>

		<div><a href="javascript:void(0);" class="resend_bulk button"><?php echo tr('Resend');?></a></div>
		<?php endif; ?>
	</div>

	<?php if ($pagination_links !== ''): ?>
	<div class="paging">
		<div class="paging"><?php  echo $pagination_links;?></div>
	</div>
	<?php endif; ?>

</div>
