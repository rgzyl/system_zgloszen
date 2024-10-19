			<?php 
				require("../../config.php");

				$stmt = $con->prepare("SELECT * FROM str_user WHERE IdUser = ?");
				$stmt->bind_param("i", $IdUser);
				$stmt->execute();
				$result = $stmt->get_result();

				while ($row = $result->fetch_assoc()) {
			?>
				<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-white sidebar shadow-sm">
					<div class="position-sticky pt-3">
						<div class="d-grid gap-2">
							<button class="btn btn-blue d-md-none" id="toggleSidebar">
								<i class="bi bi-list"></i> Menu
							</button>
						</div>
						<ul class="nav flex-column" id="sidebarContent">
							<li class="nav-item">
								<a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php' ? "active" : "") ?>" href="index.php">
									<i class="bi bi-house"></i> Strona główna
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'zgloszenia.php' ? "active" : "") ?>" href="zgloszenia.php">
									<i class="bi bi-exclamation-circle"></i> Zgłoszenia
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'zaakceptowane.php' ? "active" : "") ?>" href="zaakceptowane.php">
									<i class="bi bi-shield-check"></i> Zaakceptowane
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'historia-logowania.php' ? "active" : "") ?>" href="historia-logowania.php">
									<i class="bi bi-clock-history"></i> Historia logowania
								</a>
							</li>
							<?php 
								if ($row['role'] == 'admin') {
							?>
							<li class="nav-item">
								<a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'konta.php' ? "active" : "") ?>" href="konta.php">
									<i class="bi bi-people"></i> Konta
								</a>
							</li>
							<?php } ?>
						</ul>
					</div>
				</nav>
			<?php 
				}
				$stmt->close();
			?>
			<script>
				document.getElementById('toggleSidebar').addEventListener('click', function() {
					var sidebarContent = document.getElementById('sidebarContent');
					
					if (sidebarContent.style.display === 'block') {
						sidebarContent.style.display = 'none';
					} else {
						sidebarContent.style.display = 'block';
					}
				});
			</script>