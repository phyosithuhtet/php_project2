let navbar= document.querySelector('.header .navbar');
let menuBtn= document.querySelector('#menu-btn');

menuBtn.onclick=()=>{
    navbar.classList.toggle('active');
}
window.onscroll=()=>{
    navbar.classList.remove('active');
}
document.querySelectorAll('input[type="number"]').forEach(
    inputNumber=>{
        inputNumber.oninput=()=>{
            if(inputNumber.value.length>inputNumber.maxlength)
                inputNumber.value=inputNumber.value.slice(0, inputNumber.maxLength);
        };
    }
);