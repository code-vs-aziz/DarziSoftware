<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal fade" id="dzConfirmModal" tabindex="-1" role="dialog" aria-labelledby="dzConfirmModalTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-sm" role="document">
		<div class="modal-content dz-confirm-modal">
			<div class="modal-header">
				<h5 class="modal-title" id="dzConfirmModalTitle"><?php echo t('app_confirm'); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo esc_html(t('app_cancel')); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p id="dzConfirmModalMessage" class="dz-confirm-modal__message mb-0"></p>
			</div>
			<div class="modal-footer dz-confirm-modal__footer">
				<button type="button" class="btn btn-secondary dz-btn--outlined" data-dismiss="modal" id="dzConfirmCancel"><?php echo t('app_cancel'); ?></button>
				<button type="button" class="btn btn-primary dz-btn--filled" id="dzConfirmOk"><?php echo t('app_confirm'); ?></button>
			</div>
		</div>
	</div>
</div>
