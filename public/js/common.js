
var Formatter = {
	'remove' : function () {
		return 	[
			'<a class="remove" href="javascript:void(0)" title="Remove">',
			'<i class="bi bi-trash-fill"></i>',
			'</a>'
		].join('')
	},
	'add' : function () {
		return 	[
			'<a class="add" href="javascript:void(0)" title="add">',
			'<i class="bi bi-plus-square"></i>',
			'</a>'
		].join('')
	},
	'modify' : function () {
		return 	[
			'<a class="modify" href="javascript:void(0)" title="modify">',
			'<i class="bi bi-pencil-fill"></i>',
			'</a>'
		].join('')
	},
	'removemodify': function() {
		return Formatter.remove() + "　" + Formatter.modify()
	}
}


function backupCode() {
	var checkedRows = [];
	$('#tab-item').on('check.bs.table', function (e, row) {
		checkedRows.push({ serial: row.serial, itemName: row.itemName, itemId: row.itemId });
	});
	$('#tab-item').on('uncheck.bs.table', function (e, row) {
		$.each(checkedRows, function (index, value) {
			if (value.itemId === row.itemId) {
				checkedRows.splice(index, 1);
			}
		});
	});

	$.ajax({
		type: "POST",
		url: "inventory/remove",
		data: {"id": row.id} ,
		dataType: "JSON",
		success: function (data) {
			alert(data.msg);
			$('#table-inventory').bootstrapTable('remove', { field: 'id', values: [row.id] });		
		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			alert("Status: " + textStatus); alert("Error: " + errorThrown);
		}
	});	
}

inputEvents = {
	'change :input': function (e, value, row, index) {
		row[e.target.name] = $(e.target).prop('value');
	}
};

function inputFormatter(value, row, index, field) {
	return [
		'<input type="number" min="0" class="form-control form-control-sm" name="' + field + '" value="' + value + '"/>'
	].join('')
}

var gValidator = null;
$(function(){
	/* -----------------------------------
	 	modal
	------------------------------------- */
	
	$(".modal").on("hidden.bs.modal", function() {
		$(this).find('input,textarea,select').each(function(){
			$(this).val('').removeClass('is-invalid is-valid').prop('readonly', false);
		});

		$(this).parents("form:eq(0)").removeClass('was-validated');

		if (gValidator) {
			gValidator.resetForm();
		}
	});

	$('.modal').on('shown.bs.modal', function() {
		$(this).find('input:eq(0)').focus();
	});

	/* -----------------------------------
	 	form validation
	------------------------------------- */
	// $('.needs-validation').submit(function() {
	// 	if (!this.checkValidity()) {
	// 		return false;
	// 	 }
	// 	form.classList.add('was-validated');
	// 	return true;
	// });

	$.validator.addMethod(
		"alphaNumMix",
		function (value, element) {
			return this.optional(element) || /^(?=.*?[a-zA-Z])(?=.*?\d)[a-zA-Z\d]+$/.test(value);
		},
		"半角英数を両方含む必要があります"
	);
	$.validator.addMethod(
		"alphaNum",
		function (value, element) {
			return this.optional(element) || /^[a-zA-Z\d]+$/.test(value);
		},
		"半角英数で入力してください。"
	);
	$.validator.addMethod(
		"alphaNumSymbol",
		function (value, element) {
			return this.optional(element) || /^[a-zA-Z0-9!-/:-@¥[-`{-~]*$/.test(value);
		},
		"半角英数符号で入力してください。"
	);
	$.validator.addMethod(
		"zipCode",
		function (value, element) {
			return this.optional(element) || /^\d{3}-\d{4}$/.test(value);
		},
		"郵便番号は半角数字[***-****]の形式で入力してください。"
	);
	$.validator.addMethod(
		"tel",
		function (value, element) {
			return this.optional(element) || /^[0-9\-]+$/.test(value);
		},
		"電話番号を数字、ハイフンで入力してください。"
	);	
})

$('input[type="tel"]').on("keypress input", function(){
	let str = $(this).val();
	$(this).val(str.replace(/[^0-9\-]/g, ""));
});


/* -----------------------------------
	jquery validation 
------------------------------------- */
var validator_setting = {
	addRule : function (rules) {
		this.rules = rules;
		return this;
	},
	errorElement: "div",
	errorPlacement: function (error, element) {
		error.addClass("invalid-feedback");
		error.insertAfter(element);
	},
	highlight: function (element) {
		$(element).removeClass('is-valid').addClass('is-invalid');
	},
	unhighlight: function (element) {
		$(element).removeClass('is-invalid').addClass('is-valid');
	}
}

function ignoreValidationItem(validator, items) {
	$.each(items, function(i, val){
		delete validator.settings.rules[val];
	});
}

function addValidationItem(validator, rules) {
	Object.assign(validator.settings.rules, rules);
}