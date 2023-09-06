<style>
.toast {
	position: absolute; 
	top: 50px; 
	z-index: 999;
	margin: auto;
}
</style>

<div class="toast bg-info" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000" >
	<div class="toast-header">
		<strong class="me-auto">情報</strong>
		<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
	</div>
	<div class="toast-body">
		{{ errMsg }}
	</div>
</div>

