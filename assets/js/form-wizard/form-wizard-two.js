(function($) {
    "use strict";
    var fomr_wizard_two = {
        init: function() {
            var navListItems = $('div.setup-panel div a'),
                allWells     = $('.setup-content'),
                allNextBtn   = $('.nextBtn');

            // Ocultar todos los pasos al inicio
            allWells.hide();

            // Click en los círculos de pasos
            navListItems.click(function (e) {
                e.preventDefault();
                var $target = $($(this).attr('href')),
                    $item   = $(this);

                if (!$item.hasClass('disabled')) {
                    navListItems.removeClass('btn btn-light').addClass('btn btn-primary');
                    $item.addClass('btn btn-light');
                    allWells.hide();
                    $target.show();
                    $target.find('input:eq(0)').focus();
                }
            });

            // Botón SIGUIENTE
            allNextBtn.click(function(){
                var curStep        = $(this).closest(".setup-content"),
                    curStepBtn     = curStep.attr("id"),
                    nextStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]')
                                        .parent().next().children("a"),
                    curInputs      = curStep.find("input[type='text'],input[type='url'],input[type='email'],input[type='password']"),
                    isValid        = true;

                $(".form-group").removeClass("has-error");

                for (var i = 0; i < curInputs.length; i++) {
                    if (!curInputs[i].validity.valid) {
                        isValid = false;
                        $(curInputs[i]).closest(".form-group").addClass("has-error");
                    }
                }

                if (isValid) {
                    nextStepWizard.removeAttr('disabled').trigger('click');
                }
            });

            // Mostrar el primer paso
            $('div.setup-panel div a.btn-primary').trigger('click');
        }
    };
    (function($) {
        "use strict";
        fomr_wizard_two.init();
    })(jQuery);
})(jQuery);
