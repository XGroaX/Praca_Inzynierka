$('.my_database').click(function(){
    $('#addProductForm').toggle();
});

var isHide = true;
$('.my_cart').click(function(){
    if(isHide==true){
        $('.shop_page_container').css('right', '0');
        isHide=false;
    }else{
        $('.shop_page_container').css('right', '-400px');
        isHide=true;
    }
});


$(document).ready(function () {
    // Obsługa zdarzenia zmiany checkboxa
    $('input[name="edit_this[]"]').change(function () {
        updateButtonVisibility();
    });

    // Funkcja do aktualizacji widoczności przycisku
    function updateButtonVisibility() {
        var checkedCheckboxes = $('input[name="edit_this[]"]:checked');
        if (checkedCheckboxes.length > 0) {
            $('.delete_those').show();
        } else {
            $('.delete_those').hide();
        }
    }
});

//USUWANIE ZAZNACZONYCH PRODUKTÓW Z BAZY
document.addEventListener("DOMContentLoaded", function() {
    // Znajdź przycisk "Usuń zaznaczone produkty"
    var deleteButton = document.querySelector('.delete_those');

    // Przypisz event onClick do przycisku "Usuń zaznaczone produkty"
    deleteButton.addEventListener('click', function() {
        // Znajdź wszystkie zaznaczone checkboxy
        var checkboxes = document.querySelectorAll('.edit_this:checked');

        // Zainicjuj tablicę na zaznaczone ID produktów
        var selectedProducts = [];

        // Przejdź przez każdy zaznaczony checkbox i dodaj jego wartość do tablicy
        checkboxes.forEach(function(checkbox) {
            selectedProducts.push(checkbox.value);
        });

        // Przypisz zaznaczone ID produktów do ukrytego pola formularza
        document.getElementById('selectedProducts').value = selectedProducts.join(',');

        // Wyślij formularz
        document.getElementById('deleteForm').submit();
    });
});