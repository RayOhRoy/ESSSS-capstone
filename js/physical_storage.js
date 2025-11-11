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

    async function fetchExistingProjects() {
        try {
            const response = await fetch("model/get_existing_projects.php");
            const data = await response.json();

            if (Array.isArray(data)) {
                existingProjects = data.map(p => p.ProjectID);
                existingPrefixes = new Set(
                    data.map(p => p.ProjectID.split("-").slice(0, 3).join("-"))
                );

                // store map for storage statuses
                window.projectStorageStatus = {};
                data.forEach(p => {
                    window.projectStorageStatus[p.ProjectID] = p.StorageStatus?.toLowerCase() || "";
                });
            }
        } catch (err) {
            console.error("Error fetching project IDs:", err);
        }
    }


    const lockAPI = "https://essss-centralized-dms.com/model/lockapi.php";

    async function toggleRelay(lockNumber) {
        try {
            const response = await fetch(`${lockAPI}?endpoint=/relay&lock=${lockNumber}&action=unlock`);
            const data = await response.text();
            console.log(`Lock API [Relay ${lockNumber}] triggered:`, data);
        } catch (err) {
            console.error("Lock API connection failed:", err);
        }
    }

    function initLockToggle() {
        const lock1 = document.getElementById("lock1");
        const lock2 = document.getElementById("lock2");

        const disableBothTemporarily = () => {
            const locks = [lock1, lock2].filter(Boolean);
            locks.forEach(icon => {
                icon.style.pointerEvents = "none"; // disable clicks
                icon.dataset.originalColor = icon.style.color || ""; // store original color
                icon.style.color = "gray"; // gray out
            });

            setTimeout(() => {
                locks.forEach(icon => {
                    icon.style.pointerEvents = "auto"; // enable clicks
                    icon.style.color = icon.dataset.originalColor; // restore color
                });
            }, 10000); // 10 seconds
        };

        if (lock1 && !lock1.dataset.listenerAttached) {
            lock1.addEventListener("click", () => {
                toggleRelay(1);
                disableBothTemporarily();
            });
            lock1.dataset.listenerAttached = "true";
        }

        if (lock2 && !lock2.dataset.listenerAttached) {
            lock2.addEventListener("click", () => {
                toggleRelay(2);
                disableBothTemporarily();
            });
            lock2.dataset.listenerAttached = "true";
        }
    }

    // Update lock icons based on ESP pin states (GPIO 32 & 33)
    async function updateLockIcons() {
        try {
            const response = await fetch(`${lockAPI}?endpoint=/status&ts=${Date.now()}`);
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

            // Determine relay number
            let relayNumber = null;
            if (prefix === "HAG-01") relayNumber = 1;
            else if (prefix === "CAL-01") relayNumber = 2;

            // 🔍 Determine button label based on StorageStatus
            let storageStatus = window.projectStorageStatus?.[fullProjectId] || "";
            let buttonText = "RETRIEVE"; // default

            if (storageStatus.toLowerCase() === "stored") {
                buttonText = "RETRIEVE";
            } else if (storageStatus.toLowerCase() === "retrieve") {
                buttonText = "STORE";
            }

            return `
            <div class="envelope-card ${exists ? "" : "unavailable"}" data-projectid="${fullProjectId}">
                <div class="envelope-title">${label}</div>
                <div class="envelope-right">
                    ${exists ? `
                        <div class="preview-modal-btn fa fa-eye"></div>
                        ${(jobPosition === "cad operator" || jobPosition === "compliance officer") ? "" : `
                            <button class="fa fa-edit update-btn"
                                data-projectid="${fullProjectId}">
                            </button>
                        `}
                        <button class="envelope-button" data-relay="${relayNumber || ""}">
                            ${buttonText}
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

        document.querySelectorAll(".envelope-button[data-relay]").forEach(btn => {
            const relay = parseInt(btn.getAttribute("data-relay"));
            if (!relay) return;

            btn.addEventListener("click", async (e) => {
                e.stopPropagation();

                const buttonAction = btn.textContent.trim().toUpperCase(); // "RETRIEVE" or "STORE"
                const projectIdFull = btn.closest(".envelope-card")?.dataset.projectid || "";
                const projectIdBase = projectIdFull.split("-").slice(0, 3).join("-"); // HAG-01-001
                const cabinetName = projectIdBase.split("-").slice(0, 2).join("-");  // HAG-01

                // Disable buttons for 10s
                document.querySelectorAll(".envelope-button").forEach(b => {
                    b.disabled = true;
                    b.style.opacity = "0.5";
                });

                // 🧾 Message content (keep your version)
                let message = "";
                if (buttonAction === "RETRIEVE") {
                    message = `
                        <div><strong>${cabinetName} is now open</strong></div>
                        <div class="relay-subtext">Scan the Project QR of ${projectIdBase} to proceed with retrieval.</div>
                    `;
                } else {
                    message = `
                        <div><strong>${cabinetName} is now open</strong></div>
                        <div class="relay-subtext">Scan the Project QR of ${projectIdBase} to proceed with storage.</div>
                    `;
                }

                // ✅ Show modal with all required data
                showRelayModal(message, buttonAction, projectIdBase);

                await toggleRelay(relay);
                // Re-enable buttons after 10 seconds
                setTimeout(() => {
                    document.querySelectorAll(".envelope-button").forEach(b => {
                        b.disabled = false;
                        b.style.opacity = "1";
                    });
                }, 10000);
            });
        });


        document.querySelectorAll(".update-btn").forEach(btn => {
            btn.addEventListener("click", (e) => {
                e.stopPropagation();
                redirectToUpdate(btn);
            });
        });

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
            loadAdminPage("edit_project.php?projectId=" + encodeURIComponent(projectId));
        }
    }

    backButton.addEventListener("click", goBackToCabinets);
    backButton.style.display = "none";
    initLockToggle();
}

function showRelayModal(message, buttonAction, projectIdBase) {
    let modal = document.getElementById("relayModal");
    if (!modal) {
        modal = document.createElement("div");
        modal.id = "relayModal";
        modal.innerHTML = `
    <style>
        #relayModal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
        }
        .relay-modal-content {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            width: 90%;
            text-align: center;
        }
        #closeRelayModal {
            margin-top: 15px;
            padding: 10px 20px;
            border: none;
            background-color: #7B0302;
            color: white;
            border-radius: 8px;
            cursor: pointer;
        }
        #relayFeedback {
            margin-top: 10px; 
            color: #d33; 
            font-size: 14px;
        }
        @media screen and (max-width: 1080px) and (max-height: 2460px) {
            .relay-modal-content {
                width: 90vw;
                padding: 60px 40px;
                border-radius: 16px;
                font-size: 1.2rem;
            }
            #relayModalMsg {
                font-size: 3.2rem;
            }
            #relayModalMsg .relay-subtext {
                font-size: 2.2rem;
            }
            #relayFeedback {
                font-size: 3rem;
                margin-top: 15px;
            }
            #closeRelayModal {
                padding: 15px 25px;
                font-size: 2.5rem;
            }
        }
    </style>

    <div class="relay-modal-content">
        <p id="relayModalMsg"></p>
        <input type="text" id="relayHiddenInput" style="position:absolute; opacity:0; pointer-events:none;" />
        <div id="relayFeedback"></div>
        <button id="closeRelayModal">CANCEL</button>
    </div>
    `;
        document.body.appendChild(modal);
    }

    const hiddenInput = document.getElementById("relayHiddenInput");
    const feedback = document.getElementById("relayFeedback");
    const relayMsg = document.getElementById("relayModalMsg");

    relayMsg.innerHTML = message;
    feedback.textContent = "";
    modal.style.display = "flex";
    hiddenInput.value = "";

    const closeModal = () => {
        modal.style.display = "none";
        clearInterval(window.relayFocusInterval);
        hiddenInput.value = "";
    };

    if (window.relayFocusInterval) clearInterval(window.relayFocusInterval);
    hiddenInput.focus();
    window.relayFocusInterval = setInterval(() => hiddenInput.focus(), 1000);

    hiddenInput.onkeydown = async (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            const scannedQR = hiddenInput.value.trim();
            if (!scannedQR) return;

            try {
                const res = await fetch("model/update_project_status.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        scannedQR,
                        projectIdBase,
                        action: buttonAction
                    })
                });

                const data = await res.json();

                if (data.success) {
                    clearInterval(window.relayFocusInterval);
                    hiddenInput.blur();
                    relayMsg.textContent = "";
                    feedback.style.color = "green";
                    feedback.textContent = `Project status updated to "${data.newStatus}".`;

                    // Update button label dynamically
                    const btns = document.querySelectorAll(".envelope-button");
                    btns.forEach(btn => {
                        const card = btn.closest(".envelope-card");
                        if (card && card.dataset.projectid.startsWith(projectIdBase)) {
                            btn.textContent =
                                data.newStatus.toLowerCase() === "stored"
                                    ? "RETRIEVE"
                                    : "STORE";
                        }
                    });

                } else {
                    feedback.style.color = "#d33";
                    feedback.textContent = data.message || "Incorrect Project QR.";
                    hiddenInput.value = "";
                    hiddenInput.focus();
                }

            } catch (err) {
                console.error(err);
                feedback.style.color = "#d33";
                feedback.textContent = "Server error occurred.";
            }
        }
    };

    document.getElementById("closeRelayModal").onclick = closeModal;
    modal.onclick = (e) => {
        if (e.target === modal) closeModal();
    };
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
                    if (qrImage && project.ProjectQR) {
                        qrImage.src = project.ProjectQR;
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