function initPhysicalStorage() {
    const cardsContainer = document.querySelector(".card-container");
    const envelopeSection = document.querySelector(".envelope-section");
    const columnsContainer = document.querySelector(".envelope-columns");
    const scrollUpBtn = document.getElementById("scrollUp");
    const scrollDownBtn = document.getElementById("scrollDown");
    const topbarTitle = document.querySelector(".topbar span");
    const backButton = document.querySelector(".topbar .fa-arrow-left");

    if (!cardsContainer || !columnsContainer || !topbarTitle || !backButton) return;

    let currentPage = 1;
    const maxPages = 5;
    let currentCabinet = null;
    let existingProjects = []; // full project IDs like HAG-01-100-ABC
    let existingPrefixes = new Set(); // just prefixes like HAG-01-100

    // 🧩 Fetch existing full project IDs from database
    async function fetchExistingProjects() {
        try {
            const response = await fetch("model/get_existing_projects.php");
            const data = await response.json();

            if (Array.isArray(data)) {
                existingProjects = data; // example: ["HAG-01-100-ABC", "HAG-01-101-XYZ"]
                existingPrefixes = new Set(
                    data.map(pid => pid.split("-").slice(0, 3).join("-"))
                );
            }
        } catch (err) {
            console.error("Error fetching project IDs:", err);
        }
    }

    const espIP = "http://192.168.10.189"; // Replace with your ESP32 IP

    // Toggle relay (send unlock signal only)
    function toggleRelay(lockNumber) {
        fetch(`${espIP}/relay?lock=${lockNumber}&action=unlock`)
            .then(response => response.text())
            .then(data => console.log(`ESP [Relay ${lockNumber}] triggered:`, data))
            .catch(err => console.error("ESP connection failed:", err));
    }

    // Initialize lock click handlers
    function initLockToggle() {
        const lock1 = document.getElementById("lock1");
        const lock2 = document.getElementById("lock2");

        if (lock1) lock1.addEventListener("click", () => toggleRelay(1));
        if (lock2) lock2.addEventListener("click", () => toggleRelay(2));
    }

    // Update lock icons based on ESP pin states (GPIO 32 & 33)
    async function updateLockIcons() {
        try {
            const response = await fetch(`${espIP}/status`);
            if (!response.ok) throw new Error(`Status ${response.status}`);

            const data = await response.json();

            // Lock 1 → GPIO 32
            const lock1 = document.getElementById("lock1");
            if (lock1) {
                if (data.lock1) { // HIGH = locked
                    lock1.classList.remove("fa-unlock-alt");
                    lock1.classList.add("fa-lock");
                } else {
                    lock1.classList.remove("fa-lock");
                    lock1.classList.add("fa-unlock-alt");
                }
            }

            // Lock 2 → GPIO 33
            const lock2 = document.getElementById("lock2");
            if (lock2) {
                if (data.lock2) { // HIGH = locked
                    lock2.classList.remove("fa-unlock-alt");
                    lock2.classList.add("fa-lock");
                } else {
                    lock2.classList.remove("fa-lock");
                    lock2.classList.add("fa-unlock-alt");
                }
            }

        } catch (err) {
            console.error("Failed to update lock icons:", err);
        }
    }

    // Poll ESP32 every 500ms
    setInterval(updateLockIcons, 1000);
    updateLockIcons();
    initLockToggle();

    // 📨 Generate envelopes (20 per page, split into 2 columns)
    async function renderEnvelopes(page) {
        columnsContainer.innerHTML = "";

        const prefix = currentCabinet;
        const leftStart = (page - 1) * 10 + 1;
        const leftEnd = Math.min(leftStart + 9, 50);
        const rightStart = (page - 1) * 10 + 51;
        const rightEnd = Math.min(rightStart + 9, 100);

        const leftCol = document.createElement("div");
        leftCol.classList.add("envelope-container");
        const rightCol = document.createElement("div");
        rightCol.classList.add("envelope-container");

        const makeCard = (i) => {
            const num = String(i).padStart(3, "0");
            const label = `${prefix}-${num}`;
            const exists = existingPrefixes.has(label);
            const fullProjectId = existingProjects.find(pid => pid.startsWith(label)) || label;
            const userDataEl = document.getElementById('userData');
            const jobPosition = userDataEl?.dataset.jobposition || '';

            // Determine relay number based on prefix
            let relayNumber = null;
            if (prefix === "HAGONOY") relayNumber = 1;
            else if (prefix === "CALUMPIT") relayNumber = 2;

            return `
    <div class="envelope-card ${exists ? "" : "unavailable"}" data-projectid="${fullProjectId}">
        <div class="envelope-title">${label}</div>
        <div class="envelope-right">
            ${exists ? `
                <div class="preview-modal-btn fa fa-eye"></div>
                ${(jobPosition === "cad operator" || jobPosition === "compliance officer") ? "" : `
                    <button class="fa fa-edit update-btn"
                        data-projectid="${fullProjectId}"
                        onclick="redirectToUpdate(this)">
                    </button>
                `}
                <button class="envelope-button"
                    ${relayNumber ? `onclick="toggleRelay(${relayNumber}, this)"` : ""}>
                    RETRIEVE
                </button>
            ` : `
                <div class="fa fa-eye" style="visibility:hidden;"></div>
                <button class="envelope-button" style="visibility:hidden;">RETRIEVE</button>
            `}
        </div>
    </div>`;
        };


        for (let i = leftStart; i <= leftEnd; i++) leftCol.innerHTML += makeCard(i);
        for (let i = rightStart; i <= rightEnd; i++) rightCol.innerHTML += makeCard(i);

        columnsContainer.appendChild(leftCol);
        columnsContainer.appendChild(rightCol);

        attachEnvelopeClickHandlers();
        attachButtonClickBlockers();
        PhysicalPreview();

        scrollUpBtn.style.visibility = page === maxPages ? "hidden" : "visible";
        scrollDownBtn.style.visibility = page === 1 ? "hidden" : "visible";
    }

    // 📦 Open cabinet
    async function openCabinet(cabinetName) {
        currentCabinet = cabinetName;
        currentPage = 1;

        topbarTitle.textContent = `Physical Storage - ${cabinetName}`;
        backButton.style.display = "block";
        cardsContainer.style.display = "none";
        envelopeSection.style.display = "flex";

        await fetchExistingProjects();
        renderEnvelopes(currentPage);
    }

    // 🔙 Back to cabinet list
    function goBackToCabinets() {
        envelopeSection.style.display = "none";
        cardsContainer.style.display = "flex";
        backButton.style.display = "none";
        topbarTitle.textContent = "Physical Storage";
    }

    // Pagination
    scrollUpBtn?.addEventListener("click", () => {
        if (currentPage < maxPages) {
            currentPage++;
            renderEnvelopes(currentPage);
        }
    });

    scrollDownBtn?.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            renderEnvelopes(currentPage);
        }
    });

    // Cabinet open handler
    const openButtons = cardsContainer.querySelectorAll(".open-button");
    openButtons.forEach((btn) => {
        btn.addEventListener("click", (e) => {
            const cardTitle = e.target.closest(".card").querySelector(".card-title").textContent.trim();
            openCabinet(cardTitle);
        });
    });

    // 📄 Envelope click → open project
    // 📄 Envelope click → open project
    function attachEnvelopeClickHandlers() {
        const envelopes = document.querySelectorAll(".envelope-card");
        envelopes.forEach((card) => {
            if (!card.classList.contains("unavailable")) {
                card.addEventListener("click", () => {
                    const projectId = card.getAttribute("data-projectid");
                    if (projectId) {
                        // add view=physical
                        loadAdminPage("project.php?projectId=" + encodeURIComponent(projectId) + "&view=physical");
                    }
                });
            }
        });
    }


    function attachButtonClickBlockers() {
        const buttons = document.querySelectorAll(
            ".update-btn, .envelope-button, .preview-modal-btn"
        );
        buttons.forEach((btn) => btn.addEventListener("click", (e) => e.stopPropagation()));
    }

    // ✏️ Edit redirect → full project ID
    function redirectToUpdate(element) {
        const projectId = element.getAttribute("data-projectid");
        if (projectId) {
            loadAdminPage("update_project.php?projectId=" + encodeURIComponent(projectId));
        }
    }

    backButton.addEventListener("click", goBackToCabinets);
    backButton.style.display = "none";
    initLockToggle();
}

// 🔍 Physical Preview Modal (kept outside for global use)
function PhysicalPreview() {
    const previewButtons = document.querySelectorAll('.preview-modal-btn');
    const modal = document.getElementById('previewModal');
    const closeModalBtn = document.getElementById('closeModal');
    const openBtn = modal.querySelector('.open-btn');

    let selectedProjectIdForPreview = null;

    if (!modal || !closeModalBtn || !openBtn) return;

    previewButtons.forEach(button => {
        button.addEventListener('click', () => {
            const card = button.closest('.envelope-card');
            if (!card) return;

            const projectID = card.getAttribute('data-projectid');
            if (!projectID) return;

            selectedProjectIdForPreview = projectID;

            modal.querySelector('.preview-projectname').textContent = projectID;
            const details = modal.querySelector('.project-details');
            const docTableBody = modal.querySelector('.document-table tbody');
            if (details) details.innerHTML = '<p>Loading project details...</p>';
            if (docTableBody) docTableBody.innerHTML = '<tr><td colspan="3">Loading documents...</td></tr>';

            fetch('model/get_project_info.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ projectId: projectID })
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        if (details) details.innerHTML = `<p>Error: ${data.message}</p>`;
                        if (docTableBody) docTableBody.innerHTML = '<tr><td colspan="3">No documents found.</td></tr>';
                        return;
                    }

                    const project = data.project;

                    if (details) {
                        details.innerHTML = `
                <p><strong>Lot No.:</strong> ${project.LotNo || ''}</p>
                <p><strong>Address:</strong> ${project.FullAddress || ''}</p>
                <p><strong>Survey Type:</strong> ${project.SurveyType || ''}</p>
                <p><strong>Client:</strong> ${project.ClientName || ''}</p>
                <p><strong>Agent:</strong> ${project.Agent || 'not available'}</p>
                <p><strong>Survey Period:</strong> ${project.SurveyStartDate || ''} - ${project.SurveyEndDate || ''}</p>
                `;
                    }

                    const qrImage = modal.querySelector('.qr-img');
                    if (qrImage && project.ProjectID) {
                        qrImage.src = `uploads/${project.ProjectID}/${project.ProjectID}-QR.png`;
                        qrImage.alt = `QR Code for ${project.ProjectID}`;
                    }

                    if (docTableBody) {
                        docTableBody.innerHTML = '';
                        if (!project.documents || project.documents.length === 0) {
                            docTableBody.innerHTML = '<tr><td colspan="3">No documents found.</td></tr>';
                        } else {
                            project.documents.forEach(doc => {
                                const physicalStatusClass =
                                    doc.physical_status === 'STORED' ? 'stored' :
                                        doc.physical_status === 'RELEASED' ? 'released' : '';

                                let digitalStatusClass = '';
                                let digitalStatusText = '';
                                if (doc.digital_status === 'available') {
                                    digitalStatusClass = 'available';
                                    digitalStatusText = 'AVAILABLE';
                                    if (doc.physical_status === 'RELEASED') {
                                        digitalStatusClass += ' released';
                                    }
                                }

                                docTableBody.innerHTML += `
                    <tr>
                        <td>${doc.name}</td>
                        <td class="status ${physicalStatusClass}">${doc.physical_status || ''}</td>
                        <td class="status ${digitalStatusClass}">${digitalStatusText}</td>
                    </tr>`;
                            });
                        }
                    }

                    modal.style.display = 'block';
                })
                .catch(error => {
                    if (details) details.innerHTML = `<p>Error fetching data</p>`;
                    if (docTableBody) docTableBody.innerHTML = '<tr><td colspan="3">Error loading documents.</td></tr>';
                    console.error('Error:', error);
                });
        });
    });

    openBtn.addEventListener('click', () => {
        if (selectedProjectIdForPreview) {
            loadAdminPage('project.php?projectId=' + encodeURIComponent(selectedProjectIdForPreview));
        } else {
            alert("No project selected.");
        }
    });

    closeModalBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}