<div class="modal fade" id="inv-item-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="item-modal-label" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">

			<div class="modal-header">
				<h5 class="modal-title" id="item-modal-label">在庫商品一覧</h5>

				<select class="form-select-sm float-start ms-2 mt-2" id="warehouseId-sel" style="width:auto;">
					<option value=""> 倉庫を選んでください</option>
					{% for warehouse in warehouses %}
					<option value="{{ warehouse.id }}" {{ warehouse.id==warehouseId ? "selected" : "" }}>{{ warehouse.warehouseName }}</option>
					{% endfor %}
				</select>

				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body row">

				<!-- Table with stripped rows -->
				<table id="tab-inv-item" class="table table-striped table-sm" data-search="true" data-mobile-responsive="true" 
					data-pagination="true" data-page-size="{{settings.pagesizeSm}}" data-check-on-init="true">
					<colgroup>
						<col class="col-md-3">
						<col class="col-md-5">
						<col class="col-md-3">
						<col class="col-md-1">
					</colgroup>
					<thead>
						<tr>
							<th scope="col" data-field="serial" data-sortable="true">商品番号</th>
							<th scope="col" data-field="itemName" data-sortable="true">商品名</th>
							<th scope="col" data-field="size">サイズ</th>
							<th data-formatter='Formatter.add' data-cell-style="cellStyle" data-align="center" data-events="itemOperateEvents" data-field="operate">操作</th>
							<th scope="col" data-field="itemId" data-visible="false"></th>
							<th scope="col" data-field="warehouseId" data-visible="false"></th>
							<th scope="col" data-field="warehouseName" data-visible="false"></th>
							<th scope="col" data-field="price" data-visible="false"></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div><!-- end modal -->
<script>

	$(function() {
		$('#tab-inv-item').bootstrapTable();

		$('#warehouseId-sel').change(function () {

			const id = $(this).val();
			if (id == "") {
				return;
			};
			sendAjax("common/invitem", {id: id}, function(data) {
				$('#tab-inv-item').bootstrapTable('load', data);
			});
		});	

	})

	var hideRowItem = [];
	function cellStyle(value, row, index) {

		if ($.inArray(row['warehouseId'] + "-" + row['itemId'], hideRowItem) != -1) {
			return { classes: "hide-added" }
		} else {
			return { classes: "" }
		}
	}

	itemOperateEvents = {
		'click .add': function (e, value, row, index) {
			$(e.target).parents('td').eq(0).addClass('hide-added');
			hideRowItem.push(row['warehouseId'] + "-" + row['itemId']);

			$('#tab-detail').bootstrapTable('append', { 'serial': row['serial'], 'itemName': row['itemName'], 'itemId': row['itemId'],
														'size': row['size'], 'price': row['price'], 'warehouseId': row['warehouseId'], 'warehouseName': row['warehouseName'], 
														'quantityDb': '-', 'quantity': '' });
		}
	}

</script>