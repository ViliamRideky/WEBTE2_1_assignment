function addPlacement() {
    if ($("#placementDiv").css("display") === "none") {
        $("#placementDiv").css("display", "block");
    } else {
        $("#placementDiv").css("display", "none");
    }
}

function editPlacement() {
    if ($("#editsDiv").css("display") === "none") {
        $("#editsDiv").css("display", "block");
    } else {
        $("#editsDiv").css("display", "none");
    }
}

function editDeath() {
    if ($("#deathInfo").css("display") === "none") {
        $("#deathInfo").css("display", "block");
    } else {
        $("#deathInfo").css("display", "none");
    }
}

function addDeath() {
    if ($("#addDeathInfo").css("display") === "none") {
        $("#addDeathInfo").css("display", "block");
    } else {
        $("#addDeathInfo").css("display", "none");
    }
}

function showSnackbarOnSubmit(myForm, message, id) {
    const form = document.querySelector(myForm);
    if (form === null) {
        console.log("Form not found");
        return;
    }
    let temp = document.getElementById(id);
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const snackbar = document.createElement('div');
        snackbar.classList.add('snackbar');
        snackbar.innerText = message;
        temp.appendChild(snackbar);
        snackbar.classList.add('show');
        setTimeout(() => {
            snackbar.remove();
            form.submit();
        }, 2000);
    });
}

showSnackbarOnSubmit("#addAthlete", 'Bol pridaný nový športovec', "addos");
showSnackbarOnSubmit("#editAthlete", 'Vybraný športovec bol upravený', "editos");
showSnackbarOnSubmit("#addPlace", 'Bolo pridané nové umiestnenie', "addPlc");
showSnackbarOnSubmit("#editPlace", 'Záznam bol upravený', "editPlc");
showSnackbarOnSubmit("#welcomeForm", 'Vitajte', "welcome");

function validateName() {
    let input_name = document.getElementById("firstname");
    let validRegex = /^[a-žA-Ž]+$/;

    if (input_name.value.match(validRegex)) {
        document.getElementById("firstname").style.border = "2px solid yellowgreen"
        document.getElementById("err-name").style.display = "none";
        charsCounter.style.display = "none";
    }
    else {
        document.getElementById("firstname").style.border = "2px solid red"
        document.getElementById("err-name").style.display = "block";
    }
}

function validateSurname() {
    let input_surname = document.getElementById("lastname");
    let validRegex = /^[a-žA-Ž]+$/;

    if (input_surname.value.match(validRegex)) {
        document.getElementById("lastname").style.border = "2px solid yellowgreen"
        document.getElementById("err-surname").style.display = "none";
        charsCounter.style.display = "none"
    }
    else {
        document.getElementById("lastname").style.border = "2px solid red"
        document.getElementById("err-surname").style.display = "block";
    }
}

function validateEmail() {
    let input = document.getElementById("email");
    let validRegex = /^[a-zA-Z0-9.+-]{3,}@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*\.[a-zA-Z]{2,4}$/;

    if (input.value.match(validRegex)) {
        document.getElementById("email").style.border = "2px solid yellowgreen"
        document.getElementById("err-mail").style.display = "none";
    }
    else {
        document.getElementById("email").style.border = "2px solid red"
        document.getElementById("err-mail").style.display = "block";
    }
}

function validateLogin() {
    let input = document.getElementById("login");
    let validRegex = /^[a-zA-Z0-9.+-]{6,32}$/;

    if (input.value.match(validRegex)) {
        document.getElementById("login").style.border = "2px solid yellowgreen"
        document.getElementById("err-login").style.display = "none";
    }
    else {
        document.getElementById("login").style.border = "2px solid red"
        document.getElementById("err-login").style.display = "block";
    }
}