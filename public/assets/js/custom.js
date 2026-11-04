$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

$(document).on('click', '.btn-delete', function () {
    let button = $(this);
    let url = button.data('url');

    Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be reverted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _method: 'DELETE',
            },
            success: function (response) {
                showToast(response.message, response.status ? 'success' : 'error');
                if (typeof window.afterDeleteSuccess === 'function') {
                    window.afterDeleteSuccess(response);
                    return;
                }

                if (button.data('dt-reload') === true) {
                    let tableElement = button.closest('table');

                    if ($.fn.DataTable.isDataTable(tableElement)) {
                        tableElement.DataTable().ajax.reload(null, false);
                        return;
                    }
                }

                if ($.fn.DataTable) {
                    // $('.dataTable').each(function() {
                    //     $(this).DataTable().ajax.reload(null, false);
                    // });
                    // return;
                }

                location.reload();
            },
            error: function () {
                showToast('Something went wrong.', 'error');
            }
        });
    });
});


// Forms with data-confirm="question" ask with SweetAlert before submitting
// Optional: data-confirm-button="Yes, remove it!", data-confirm-color="#198754"
$(document).on('submit', 'form[data-confirm]', function (e) {
    e.preventDefault();

    const form = this;
    const submitter = e.originalEvent?.submitter;

    Swal.fire({
        title: 'Are you sure?',
        text: $(form).data('confirm'),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: $(form).data('confirm-color') || '#d33',
        confirmButtonText: $(form).data('confirm-button') || 'Yes, continue',
    }).then((result) => {
        if (!result.isConfirmed) return;

        // form.submit() drops the clicked button's name/value, so carry it over
        if (submitter && submitter.name) {
            $('<input type="hidden">').attr('name', submitter.name).val(submitter.value).appendTo(form);
        }

        // Native submit doesn't fire the submit event again
        form.submit();
    });
});


const showToast = (text, type = 'success', duration = 3000) => {
    const typeLower = type.toLowerCase();

    if (!['success', 'error'].includes(typeLower)) {
        /* eslint-disable-next-line */
        console.error('The valid value for toast are: success, error');
        return false;
    }

    const background = typeLower === 'success' ? "linear-gradient(to right, #11823B, #11826D)" : "linear-gradient(to right, #FF0000, #FF6400)";

    /* eslint-disable-next-line */
    Toastify({
        text,
        duration,
        close: true,
        gravity: 'top', // `top` or `bottom`
        position: 'right', // `left`, `center` or `right`
        stopOnFocus: true, // Prevents dismissing of toast on hover
        style: {
            background,
        },
    }).showToast(type);
};

if ($(".flat-datepicker").length) {
    $(".flat-datepicker").flatpickr({
        allowInput: false,
        altInput: true,
        dateFormat: "Y-m-d",
        altFormat: "d-m-Y",
    });
}

// Searchable customer dropdown: <select class="customer-select"> with options from the
// customers/_options partial (Tom Select copies each option's data-mobile / data-no, both searchable)
$("select.customer-select").each(function () {
    var escape = function (text) {
        return $("<div>").text(text || "").html();
    };

    new TomSelect(this, {
        searchField: ["text", "mobile", "no"],
        maxOptions: 50,
        placeholder: "Type name, mobile or CUS no",
        render: {
            option: function (data) {
                return '<div><div>' + escape(data.text) + '</div><small class="text-muted">' +
                    escape(data.no) + (data.mobile ? ' &middot; ' + escape(data.mobile) : '') + '</small></div>';
            },
            item: function (data) {
                return '<div>' + escape(data.text) + (data.no ? ' <small class="text-muted ms-1">' + escape(data.no) + '</small>' : '') + '</div>';
            },
            no_results: function () {
                return '<div class="no-results">No customer found</div>';
            },
        },
    });
});
function cleanMobile(value) {
    var digits = String(value || "").replace(/\D/g, "");
    if (digits.length === 12 && digits.startsWith("91")) digits = digits.slice(2);
    if (digits.length === 11 && digits.startsWith("0")) digits = digits.slice(1);
    return digits.slice(0, 10);
}

$(document).on("input", ".mobile-input", function () {
    $(this).val(cleanMobile($(this).val()));
});

function formatTruckNo(value) {
    value = String(value || "").toUpperCase().replace(/[^A-Z0-9]/g, "");
    var formatted = "";
    if (value.length > 0) formatted += value.substring(0, 2).replace(/[^A-Z]/g, "");
    if (value.length > 2) formatted += value.substring(2, 4).replace(/[^0-9]/g, "");
    if (value.length > 4) formatted += value.substring(4, 6).replace(/[^A-Z]/g, "");
    if (value.length > 6) formatted += value.substring(6, 10).replace(/[^0-9]/g, "");
    return formatted;
}

function pickedOption(input) {
    var list = input.list;
    if (!list) return null;
    return Array.prototype.find.call(list.options, function (option) {
        return option.value === input.value;
    }) || null;
}

function autoFill($input, value) {
    var current = $input.val();
    if (current === "" || current === $input.data("autofilled")) {
        $input.val(value || "").data("autofilled", value || "");
        $input.removeClass("is-invalid").siblings(".invalid-feedback").remove();
    }
}

$(document).on("input", "input.truck-no", function () {
    var formatted = formatTruckNo($(this).val());
    if ($(this).val() !== formatted) $(this).val(formatted);

    var option = pickedOption(this);
    if (option && option.dataset.name) {
        var $form = $(this).closest("form");
        autoFill($form.find("input.driver-name"), option.dataset.name);
        autoFill($form.find("input.driver-mobile"), option.dataset.mobile);
    }
});

$(document).on("input", "input.driver-name", function () {
    $(this).data("autofilled", null);

    var option = pickedOption(this);
    if (option) {
        var $mobile = $(this).closest("form").find("input.driver-mobile");
        $(this).val(option.dataset.name);
        $mobile.val(option.dataset.mobile || "").data("autofilled", option.dataset.mobile || "");
        $mobile.removeClass("is-invalid").siblings(".invalid-feedback").remove();
    }
});

$(document).on("input", "input.driver-mobile", function () {
    $(this).data("autofilled", null);
});

function filterItemTypes($row, reset) {
    var $type = $row.find("select.item-type");
    if (reset) $type.val("");
    $type.find("option:not(:first)").addClass("d-none");
    $type.find('option[data-brand_id="' + $row.find("select.item-brand").val() + '"]').removeClass("d-none");
}

$(document).on("change", "select.item-brand", function () {
    filterItemTypes($(this).closest("tr"), true);
});

$(function () {
    $("select.item-brand").each(function () {
        filterItemTypes($(this).closest("tr"), false);
    });
});
