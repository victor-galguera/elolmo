(function ($) {
    Drupal.behaviors.mymodule = {
      attach: function (context, settings) {
        $('.field-name-field-rate').on('keyup change', function (){
          var rate = $('.field-name-field-rate input').val();
          var quantity = $('.field-name-field-quantity input').val();
          var vat= (quantity * rate).toFixed(2);
  
          $('.field-name-field-vat input').val(vat);
        });
    }
}
});