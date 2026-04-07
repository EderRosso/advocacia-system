            </main> <!-- Fecha .content-container -->
        </div> <!-- Fecha .main-content -->
    </div> <!-- Fecha .layout-wrapper -->

    <!-- Scripts -->
    <script src="<?= BASE_URL ?>assets/js/script.js"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?= BASE_URL ?>sw.js')
                .then(registration => {
                    console.log('ServiceWorker registrado com sucesso: ', registration.scope);
                }, err => {
                    console.log('Falha ao registrar o ServiceWorker: ', err);
                });
            });
        }

        // ==========================================
        // 1. Alternador do Tema Escuro / Claro
        // ==========================================
        const themeBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        
        function updateThemeIcon() {
            if (document.documentElement.getAttribute('data-theme') === 'dark') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
        }
        
        if(themeBtn) {
            updateThemeIcon();
            themeBtn.addEventListener('click', () => {
                let currentTheme = document.documentElement.getAttribute('data-theme');
                let targetTheme = currentTheme === 'light' ? 'dark' : 'light';
                
                document.documentElement.setAttribute('data-theme', targetTheme);
                localStorage.setItem('advTheme', targetTheme);
                updateThemeIcon();
            });
        }

        // ==========================================
        // 2. SweetAlert2: Wrapper Global de Alertas
        // ==========================================
        window.confirmDialog = function(event, url, message) {
            event.preventDefault();
            Swal.fire({
                title: 'Atenção',
                text: message || "Você tem certeza que deseja realizar esta ação?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, executar!',
                cancelButtonText: 'Cancelar',
                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e1e1e' : '#fff',
                color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#545454'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
            return false;
        };

        // Transformar Alertas Nativos (success/danger) em Toasts Flutuantes automaticamente
        document.addEventListener('DOMContentLoaded', () => {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true,
                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e1e1e' : '#fff',
                color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#545454'
            });

            const successAlert = document.querySelector('.alert-success');
            const errorAlert = document.querySelector('.alert-danger');

            if (successAlert) {
                successAlert.style.display = 'none';
                Toast.fire({ icon: 'success', title: successAlert.innerText });
            }
            if (errorAlert) {
                errorAlert.style.display = 'none';
                Toast.fire({ icon: 'error', title: errorAlert.innerText });
            }

            // ==========================================
            // 3. Micro-Animações: Conta-giros numérico
            // ==========================================
            const animateNumbers = document.querySelectorAll('.animate-number');
            animateNumbers.forEach(el => {
                const target = +el.getAttribute('data-target');
                if(!isNaN(target)) {
                    let current = 0;
                    // Incremento suavizado baseado no alvo (mais rápido para números maiores)
                    const increment = target / 50; 
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            el.innerText = target.toLocaleString('pt-BR');
                            clearInterval(timer);
                        } else {
                            el.innerText = Math.ceil(current).toLocaleString('pt-BR');
                        }
                    }, 20);
                }
            });
        });

    </script>
</body>
</html>
