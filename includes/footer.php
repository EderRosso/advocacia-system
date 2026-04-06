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
    </script>
</body>
</html>
