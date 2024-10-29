<?php $this->load->helper('inflector'); ?>

<div class="bttn-container">
	<?php
	if ($this->uri->segment(2) === 'conversation'):
		if ($this->uri->segment(3) === 'folder'):

			// _tni_ added this for translation on the inbox, outbox etc.
			$theFolder = $this->uri->segment(4);
			$theFname = 'inbox';
			if ($theFolder === 'inbox')
			{
				$theFname = tr_raw('Inbox');
			}
			else
			{
				if ($theFolder === 'outbox')
				{
					$theFname = tr_raw('Outbox');
				}
				else
				{
					if ($theFolder === 'sentitems')
					{
						$theFname = tr_raw('Sent items');
					}
					else
					{
						//$theFname = $this->Kalkun_model->get_folders('name', $this->uri->segment(4))->row('name');
						$theFname = $this->uri->segment(4);
					}
				}
			}
	?>
	<div class="bttn-group">
		<button><?php echo anchor('messages/folder/'.$this->uri->segment(4), '&lsaquo;&lsaquo; '.tr('Back to {0}', NULL, $theFname), array('class' => 'button', 'id' => 'back_threadlist'));?></button>
	</div>
	<?php else: ?>
	<div class="bttn-group">
		<button><?php echo anchor('messages/my_folder/'.$this->uri->segment(4).'/'.$this->uri->segment(6), '&lsaquo;&lsaquo; '.tr('Back to {0}', NULL, humanize($this->Kalkun_model->get_folders('name', $this->uri->segment(6))->row('name'))), array('class' => 'button', 'id' => 'back_threadlist'));?></button>
	</div>

	<?php endif;?>
	<?php endif;?>
	<div class="bttn-group">
		<button><a href="javascript:void(0);" class="select_all_button button"><?php echo tr('Select all');?></a></button>
		<button><a href="javascript:void(0);" class="clear_all_button button"><?php echo tr('Deselect all');?></a></button>
	</div>

	<div class="bttn-group">
		<?php if ($this->uri->segment(2) === 'conversation' && $this->uri->segment(4) === 'inbox') :
			if ($this->uri->segment(6) !== '6') : ?>
		<button><a href="javascript:void(0);" class="spam_button button"><?php echo tr('Report spam');?></a></button>
		<?php   else : ?>
		<button><a href="javascript:void(0);" class="ham_button button"><?php echo tr('Not spam');?></a></button>
		<?php   endif;
		endif;?>
	</div>

	<div class="bttn-group">
		<?php
	if ($this->uri->segment(2) === 'folder' && $this->uri->segment(3) === 'outbox'):
	elseif ($this->uri->segment(2) === 'conversation' && $this->uri->segment(4) === 'outbox'):
	else:?>

		<?php if ($this->uri->segment(4) === '5' or $this->uri->segment(6) === '5') : ?>
		<button><a href="javascript:void(0);" class="recover_button button"><?php echo tr('Recover');?></a></button>
		<?php endif; ?>
		<button><a class="move_to_button button" href="javascript:void(0);"><?php echo tr('Move to');?></a></button>
		<?php endif; ?>
		<button><a class="global_delete button" href="javascript:void(0);">
				<?php
	if ($this->uri->segment(4) === '5' or $this->uri->segment(6) === '5' or $this->uri->segment(4) === '6' or $this->uri->segment(6) === '6'):
		echo tr('Delete permanently');
	else:
		echo tr('Delete');
	endif;
	?></a></button>
	</div>
	<?php if ($this->uri->segment(2) !== 'search'): ?>

	<div class="bttn-group">
		<button><a href="javascript:void(0);" class="refresh_button button"><?php echo tr('Refresh');?></a></button>
	</div>
	<?php endif; ?>

	<div class="bttn-group">
		<?php if ($this->uri->segment(2) === 'conversation' && $this->uri->segment(4) === 'sentitems'): ?>

		<button><a href="javascript:void(0);" class="resend_bulk button"><?php echo tr('Resend');?></a></button>
		<?php endif; ?>

		<?php if ($pagination_links !== ''): ?>
		<button class="paging">
			<div id="paging"><?php  echo $pagination_links;?></div>
		</button>
		<?php endif; ?>
	</div>

</div>
