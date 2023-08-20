<div class="modal fade" id="customer-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="customer-modal-label" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">

			<div class="modal-header">
				<h5 class="modal-title" id="customer-modal-label">取引先一覧</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body row">

				<!-- Table with stripped rows -->
				<table id="tab-customer" class="table table-striped table-sm" data-search="true" data-mobile-responsive="true" 
					data-pagination="true" data-page-size="{{settings.pagesizeSm}}" data-check-on-init="true">
					<colgroup>
						<col class="col-md-3">
						<col class="col-md-3">
						<col class="col-md-5">
						<col class="col-md-1">
					</colgroup>
					<thead>
						<tr>
							<th scope="col" data-field="name" data-sortable="true">取引先名</th>
							<th scope="col" data-field="tel" data-sortable="true">TEL</th>
							<th scope="col" data-field="address">住所</th>
							<th data-formatter='Formatter.add' data-align="center" data-events="customerOperateEvents" data-field="operate">操作</th>
							<th scope="col" data-field="id" data-visible="false"></th>
						</tr>
					</thead>
					<tbody>
						{% for customer in customers %}
						<tr>
							<td>{{ customer.name}}</td>
							<td>{{ customer.tel}}</td>
							<td>{{ customer.address}}</td>
							<td></td>
							<td>{{ customer.id}}</td>
						</tr>
						{% endfor %}
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div><!-- end modal -->
<script>

	$(function(){
		$('#tab-customer').bootstrapTable();
	})

	customerOperateEvents = {
		'click .add': function (e, value, row, index) {
			if (typeof customerCallback === "function") {
				$('#customer-modal').modal('toggle');
				customerCallback(row);
			}
		}
	}
</script>