//inpute date -> the label disapears
document.addEventListener('DOMContentLoaded',function (){
    const date_input = document.getElementById('date-input');
    const label = document.getElementById('date-label');

    date_input.addEventListener('change', function (){
        //checks if a date is set
        if(date_input.value !== ""){
            //label disappears when date is set
            label.style.display = 'none';
            date_input.style.color = 'black';
        }
        else{
            //when date is clear then back to "normal"
            label.style.display = 'flex';
            date_input.style.color = 'transparent';
        }
    })
})
