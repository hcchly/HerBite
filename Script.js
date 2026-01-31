// SIGN UP -> go to login page
const signupForm = document.getElementById("signupForm");
if (signupForm) {
  document.getElementById("profileImg").setAttribute("accept", "image/*"); // images only

  signupForm.addEventListener("submit", function (e) {
    e.preventDefault();
    window.location.href = "user.html";
  });
}

// LOGIN USER -> go to user page
const loginForm = document.getElementById("loginForm");
if (loginForm) {
  loginForm.addEventListener("submit", function (e) {
    e.preventDefault();
    window.location.href = "user.html";
  });

  // LOGIN ADMIN -> go to admin page
  document.getElementById("adminBtn").addEventListener("click", function () {
    window.location.href = "admin.html";
  });
}

console.log("User Page loaded (Phase 1).");
// Category filtering will be implemented in Phase 2.

document.addEventListener("DOMContentLoaded", () => {
  const showBtn = document.getElementById("showPlateBtn");
  const resetBtn = document.getElementById("resetBtn");
  const startBtn = document.getElementById("startCookingBtn");
  const plateLayers = document.getElementById("plateLayers");
  const counter = document.getElementById("selectedCount");
  const cookingSection = document.getElementById("cookingSection");

  if (!showBtn || !resetBtn || !startBtn || !plateLayers || !counter || !cookingSection) {
    console.log("Missing elements. Check IDs.");
    return;
  }

  let currentIndex = 0;

  
  const ingredients = [
    { name: "Fruits", src: "Fruits.PNG", top: "35%", left: "50%", width: "40%" },
    { name: "Chia",   src: "Chia.PNG",   top: "58%", left: "52%", width: "40%" },
    { name: "Honey",  src: "Honey.PNG",  top: "62%", left: "48%", width: "22%" }
  ];

  function placeIngredient(ing){
    const img = document.createElement("img");
    img.src = ing.src;
    img.alt = ing.name;
    img.className = "plate-item";
    img.style.top = ing.top;
    img.style.left = ing.left;
    img.style.width = ing.width;
    plateLayers.appendChild(img);
  }

  // Add Ingredients
  showBtn.addEventListener("click", () => {
    if (currentIndex >= ingredients.length) return;

    placeIngredient(ingredients[currentIndex]);
    currentIndex++;
    counter.textContent = String(currentIndex);

    if (currentIndex === ingredients.length) {
      startBtn.disabled = false;
      startBtn.removeAttribute("disabled");

      showBtn.textContent = "All Added";
      showBtn.classList.add("is-done");
      showBtn.disabled = true;
    }
  });

  // Show / Hide Preparation Steps
  startBtn.addEventListener("click", () => {
    const hidden = cookingSection.style.display === "none" || cookingSection.style.display === "";

    if (hidden) {
      cookingSection.style.display = "block";
      startBtn.textContent = "Hide Preparation Steps";
      cookingSection.scrollIntoView({ behavior: "smooth", block: "start" });
    } else {
      cookingSection.style.display = "none";
      startBtn.textContent = "Show Preparation Steps";
    }
  });

  // Reset
  resetBtn.addEventListener("click", () => {
    plateLayers.innerHTML = "";
    currentIndex = 0;
    counter.textContent = "0";

    showBtn.disabled = false;
    showBtn.classList.remove("is-done");
    showBtn.textContent = "Add Ingredients";

    startBtn.disabled = true;
    startBtn.setAttribute("disabled", "disabled");
    startBtn.textContent = "Show Preparation Steps";

    cookingSection.style.display = "none";
  });
});



let currentStep = 0;
const stepss = document.querySelectorAll(".step");
const progressBars = document.querySelectorAll(".progress div");

function showStep(n){
  stepss.forEach((s,i)=>s.classList.toggle("active", i===n));
  progressBars.forEach((p,i)=>p.classList.toggle("done", i<=n));
}

function nextStep(){
  if(currentStep < stepss.length-1){
    currentStep++;
    showStep(currentStep);
  }
}

function prevStep(){
  if(currentStep > 0){
    currentStep--;
    showStep(currentStep);
  }
}

function addIngredient(){
  const div = document.createElement("div");
  div.innerHTML = 'Name: <input type="text" required> Quantity: <input type="text" required>';
  document.getElementById("ingredients").appendChild(div);
}

function addStep(){
  const count = document.querySelectorAll("#steps div").length + 1;
  const div = document.createElement("div");
  div.innerHTML = 'Step ' + count + ': <input type="text" required>';
  document.getElementById("steps").appendChild(div);
}


/* =========================
   ADD RECIPE (Tabs Stepper)
========================= */
document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll(".ar-tab");
  const steps = document.querySelectorAll(".ar-step");
  const prevBtn = document.getElementById("prevBtn");
  const nextBtn = document.getElementById("nextBtn");
  const submitBtn = document.getElementById("submitBtn");

  const ingredientsWrap = document.getElementById("ingredientsWrap");
  const stepsWrap = document.getElementById("stepsWrap");
  const addIngredientBtn = document.getElementById("addIngredientBtn");
  const addStepBtn = document.getElementById("addStepBtn");

  
  if (!tabs.length || !steps.length || !prevBtn || !nextBtn || !submitBtn) return;
 
  if (!document.querySelector(".ar-tab")) return;

  let currentAdd = 0;

  function setActiveStepAdd(i) {
    currentAdd = i;

    steps.forEach(s => s.classList.remove("is-active"));
    steps[currentAdd].classList.add("is-active");

    tabs.forEach(t => t.classList.remove("is-active"));
    tabs[currentAdd].classList.add("is-active");

    prevBtn.disabled = currentAdd === 0;

    const last = currentAdd === steps.length - 1;
    nextBtn.style.display = last ? "none" : "inline-block";
    submitBtn.style.display = last ? "inline-block" : "none";
  }

  tabs.forEach(tab => {
    tab.addEventListener("click", () => setActiveStepAdd(Number(tab.dataset.step)));
  });

  nextBtn.addEventListener("click", () => {
    if (currentAdd < steps.length - 1) setActiveStepAdd(currentAdd + 1);
  });

  prevBtn.addEventListener("click", () => {
    if (currentAdd > 0) setActiveStepAdd(currentAdd - 1);
  });

  if (addIngredientBtn && ingredientsWrap) {
    addIngredientBtn.addEventListener("click", () => {
      const row = document.createElement("div");
      row.className = "ar-row";
      row.innerHTML = `
        <div>
          <label class="ar-mini">Ingredient Name</label>
          <input class="ar-input" type="text" required>
        </div>
        <div>
          <label class="ar-mini">Quantity</label>
          <input class="ar-input" type="text" required>
        </div>
      `;
      ingredientsWrap.appendChild(row);
    });
  }

  if (addStepBtn && stepsWrap) {
    addStepBtn.addEventListener("click", () => {
      const count = stepsWrap.querySelectorAll(".ar-row1").length + 1;
      const row = document.createElement("div");
      row.className = "ar-row1";
      row.innerHTML = `
        <label class="ar-mini">Step ${count}</label>
        <input class="ar-input" type="text" required>
      `;
      stepsWrap.appendChild(row);
    });
  }

  setActiveStepAdd(0);
});


/* =========================
   EDIT RECIPE 
========================= */
document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll(".er-tab");
  const steps = document.querySelectorAll(".er-step");
  const prevBtn = document.getElementById("prevBtn");
  const nextBtn = document.getElementById("nextBtn");
  const submitBtn = document.getElementById("submitBtn");

  const ingredientsWrap = document.getElementById("ingredientsWrap");
  const stepsWrap = document.getElementById("stepsWrap");
  const addIngredientBtn = document.getElementById("addIngredientBtn");
  const addStepBtn = document.getElementById("addStepBtn");

 
  if (!tabs.length || !steps.length || !prevBtn || !nextBtn || !submitBtn) return;
 
  if (!document.querySelector(".er-tab")) return;

  let currentEdit = 0;

  function setActiveStepEdit(i) {
    currentEdit = i;

    steps.forEach(s => s.classList.remove("is-active"));
    steps[currentEdit].classList.add("is-active");

    tabs.forEach(t => t.classList.remove("is-active"));
    tabs[currentEdit].classList.add("is-active");

    prevBtn.disabled = currentEdit === 0;

    const last = currentEdit === steps.length - 1;
    nextBtn.style.display = last ? "none" : "inline-block";
    submitBtn.style.display = last ? "inline-block" : "none";
  }

  tabs.forEach(tab => {
    tab.addEventListener("click", () => setActiveStepEdit(Number(tab.dataset.step)));
  });

  nextBtn.addEventListener("click", () => {
    if (currentEdit < steps.length - 1) setActiveStepEdit(currentEdit + 1);
  });

  prevBtn.addEventListener("click", () => {
    if (currentEdit > 0) setActiveStepEdit(currentEdit - 1);
  });

  if (addIngredientBtn && ingredientsWrap) {
    addIngredientBtn.addEventListener("click", () => {
      const row = document.createElement("div");
      row.className = "er-row";
      row.innerHTML = `
        <div>
          <label class="er-mini">Ingredient Name</label>
          <input class="er-input" type="text" required>
        </div>
        <div>
          <label class="er-mini">Quantity</label>
          <input class="er-input" type="text" required>
        </div>
      `;
      ingredientsWrap.appendChild(row);
    });
  }

  if (addStepBtn && stepsWrap) {
    addStepBtn.addEventListener("click", () => {
      const count = stepsWrap.querySelectorAll(".er-row1").length + 1;
      const row = document.createElement("div");
      row.className = "er-row1";
      row.innerHTML = `
        <label class="er-mini">Step ${count}</label>
        <input class="er-input" type="text" required>
      `;
      stepsWrap.appendChild(row);
    });
  }

  setActiveStepEdit(0);
});
