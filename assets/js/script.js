document.addEventListener("DOMContentLoaded", function() {
    const menuToggle = document.getElementById("menuToggle");
    const sidebar = document.getElementById("sidebar");

        const sidebarOverlay = document.getElementById("sidebarOverlay");

        if (menuToggle && sidebar) {
            menuToggle.addEventListener("click", function() {
                sidebar.classList.toggle("active");
                if (sidebarOverlay) {
                    sidebarOverlay.classList.toggle("active");
                }
            });

            const closeSidebarBtn = document.getElementById("closeSidebar");
            if (closeSidebarBtn) {
                closeSidebarBtn.addEventListener("click", function() {
                    sidebar.classList.remove("active");
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.remove("active");
                    }
                });
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener("click", function() {
                    sidebar.classList.remove("active");
                    sidebarOverlay.classList.remove("active");
                });
            }

            // Fechar menu se clicar fora (para mobile) - Mantido para compatibilidade
            document.addEventListener("click", function(event) {
                if (window.innerWidth <= 768 && !sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
                    sidebar.classList.remove("active");
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.remove("active");
                    }
                }
            });
        }

    // Formatação de Tabelas Responsivas: Criar cartões empilhados (Stacked Cards) no Mobile
    const responsiveTables = document.querySelectorAll(".table-responsive .table");
    responsiveTables.forEach(table => {
        const headers = Array.from(table.querySelectorAll("thead th")).map(th => th.innerText.trim());
        const rows = table.querySelectorAll("tbody tr");
        
        rows.forEach(row => {
            const cells = row.querySelectorAll("td");
            cells.forEach((cell, index) => {
                // Ignore empty cols or specific empty states like "colspan" (no content)
                if (headers[index] && cell.getAttribute("colspan") == null) {
                    cell.setAttribute("data-label", headers[index]);
                }
            });
        });
    });

    // Máscara básica para CPF, Telefone e CEP poderiam ir aqui
});
