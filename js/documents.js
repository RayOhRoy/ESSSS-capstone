const municipalities = {
  Bulacan: ["Hagonoy", "Calumpit", "Malolos City", "Baliuag"],
  Pampanga: ["Angeles City", "Apalit", "Guagua", "Lubao"]
};

function showMunicipalities(button) {
  const selectedProvince = button.getAttribute("data-province");
  const muniContainer = document.getElementById("municipalityButtons");
  const provinceContainer = document.getElementById("provinceButtons");
  const backBtn = document.getElementById("backBtn");

  // Hide province buttons
  provinceContainer.style.display = "none";

  // Show Back button
  backBtn.style.display = "inline-block";

  // Show municipalities
  muniContainer.innerHTML = "";
  muniContainer.style.display = "block";

  municipalities[selectedProvince].forEach(muni => {
    const btn = document.createElement("button");
    btn.textContent = muni;
    btn.classList.add("btns"); // ✅ Add your styling class
    btn.setAttribute("data-municipality", muni);
    btn.onclick = function() {
      redirectToProjectList(this);
    };
    muniContainer.appendChild(btn);
  });
}

function showProvinces() {
  // Hide municipalities and back button, show provinces again
  document.getElementById("municipalityButtons").style.display = "none";
  document.getElementById("backBtn").style.display = "none";
  document.getElementById("provinceButtons").style.display = "block";
}

function redirectToProjectList(button) {
  const municipality = button.getAttribute("data-municipality");
  loadAdminPage("project_list.php?municipality=" + encodeURIComponent(municipality));
}
