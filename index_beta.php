<!-- Encabezado mejorado con mejor menú y navegación -->
<style>
/* Estilos adicionales para el header mejorado */
.navbar-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    padding: 1rem 0;
    border-bottom: 3px solid rgba(255,255,255,0.1);
}

.navbar-brand {
    font-weight: 700;
    font-size: 1.5rem;
    color: white !important;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.navbar-brand .brand-icon {
    background: rgba(255,255,255,0.2);
    padding: 0.5rem;
    border-radius: 10px;
    backdrop-filter: blur(10px);
}

.nav-menu {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-nav {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    color: white;
    padding: 0.6rem 1.2rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    white-space: nowrap;
}

.btn-nav:hover {
    background: rgba(255,255,255,0.25);
    border-color: rgba(255,255,255,0.4);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.btn-nav.btn-primary-alt {
    background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);
    border-color: transparent;
}

.btn-nav.btn-primary-alt:hover {
    background: linear-gradient(135deg, #0072ff 0%, #00c6ff 100%);
    transform: translateY(-2px) scale(1.05);
}

.dropdown-nav {
    position: relative;
}

.dropdown-toggle-nav {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    color: white;
    padding: 0.6rem 1.2rem;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.dropdown-toggle-nav:hover {
    background: rgba(255,255,255,0.25);
    border-color: rgba(255,255,255,0.4);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.dropdown-menu-nav {
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    border: none;
    padding: 0.5rem 0;
    min-width: 220px;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    margin-top: 0.5rem;
}

.dropdown-nav.show .dropdown-menu-nav {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-item-nav {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.25rem;
    color: #333;
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.dropdown-item-nav:hover {
    background: linear-gradient(90deg, #f8f9ff 0%, #e3f2fd 100%);
    color: #667eea;
    border-left-color: #667eea;
    transform: translateX(5px);
}

.dropdown-item-nav .icon {
    width: 20px;
    text-align: center;
    font-size: 1rem;
}

.user-section {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-left: auto;
}

.user-info-card {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 12px;
    padding: 0.75rem 1rem;
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    background: rgba(255,255,255,0.3);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: white;
    font-size: 1.1rem;
}

.user-details {
    display: flex;
    flex-direction: column;
    color: white;
}

.user-name {
    font-weight: 600;
    font-size: 0.9rem;
    margin: 0;
}

.user-role {
    font-size: 0.75rem;
    opacity: 0.8;
    margin: 0;
}

.btn-logout {
    background: rgba(255,107,107,0.2);
    border: 1px solid rgba(255,107,107,0.4);
    color: white;
    padding: 0.6rem 1rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.btn-logout:hover {
    background: rgba(255,107,107,0.3);
    border-color: rgba(255,107,107,0.6);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255,107,107,0.3);
}

/* Responsividad mejorada */
@media (max-width: 1200px) {
    .nav-menu {
        gap: 0.75rem;
    }
    
    .btn-nav, .dropdown-toggle-nav {
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
    }
}

@media (max-width: 992px) {
    .navbar-custom {
        padding: 0.75rem 0;
    }
    
    .nav-menu {
        gap: 0.5rem;
        margin-top: 1rem;
        width: 100%;
        justify-content: center;
    }
    
    .user-section {
        margin-left: 0;
        margin-top: 1rem;
        width: 100%;
        justify-content: space-between;
    }
    
    .user-info-card {
        flex: 1;
        margin-right: 1rem;
    }
}

@media (max-width: 768px) {
    .navbar-brand {
        font-size: 1.25rem;
    }
    
    .nav-menu {
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
    }
    
    .btn-nav, .dropdown-toggle-nav {
        width: 100%;
        justify-content: center;
        text-align: center;
    }
    
    .user-section {
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
    }
    
    .user-info-card, .btn-logout {
        width: 100%;
        justify-content: center;
    }
    
    .dropdown-menu-nav {
        position: relative;
        box-shadow: none;
        border: 1px solid #e9ecef;
        margin-top: 0.5rem;
        opacity: 1;
        visibility: visible;
        transform: none;
    }
}

/* Indicador de página activa */
.btn-nav.active {
    background: rgba(255,255,255,0.3);
    border-color: rgba(255,255,255,0.5);
    box-shadow: 0 0 15px rgba(255,255,255,0.3);
}
</style>

<!-- Header mejorado -->
<nav class="navbar navbar-custom">
    <div class="container">
        <div class="row w-100 align-items-center">
            <!-- Brand -->
            <div class="col-lg-3 col-md-12 mb-lg-0 mb-3">
                <a class="navbar-brand" href="index.php">
                    <div class="brand-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div>
                        <div>Visualizar Horarios</div>
                        <small style="font-size: 0.7rem; opacity: 0.8;">Sistema de Gestión</small>
                    </div>
                </a>
            </div>

            <!-- Navegación principal -->
            <div class="col-lg-6 col-md-12 mb-lg-0 mb-3">
                <div class="nav-menu">
                    <?php if (isset($_SESSION["id_tipo"]) && ($_SESSION["id_tipo"] == 0 || $_SESSION["id_tipo"] == 2)): ?>
                        
                        <!-- Botón Horarios Libres -->
                        <a href="horarios_libres.php" class="btn-nav btn-primary-alt">
                            <i class="fas fa-search"></i>
                            <span>Horarios Libres</span>
                        </a>

                        <!-- Dropdown Administración -->
                        <div class="dropdown-nav">
                            <div class="dropdown-toggle-nav" onclick="toggleDropdown(this)">
                                <i class="fas fa-cog"></i>
                                <span>Administración</span>
                                <i class="fas fa-chevron-down" style="transition: transform 0.3s ease;"></i>
                            </div>
                            <div class="dropdown-menu-nav">
                                <div style="padding: 0.5rem 1rem; border-bottom: 1px solid #eee; margin-bottom: 0.5rem;">
                                    <small style="color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                        Gestión por día
                                    </small>
                                </div>
                                <a class="dropdown-item-nav" href="lunes/index.php">
                                    <i class="fas fa-calendar-day icon" style="color: #667eea;"></i>
                                    <div>
                                        <div style="font-weight: 500;">Lunes</div>
                                        <small style="color: #666;">Administrar horarios</small>
                                    </div>
                                </a>
                                <a class="dropdown-item-nav" href="martes/index.php">
                                    <i class="fas fa-calendar-day icon" style="color: #f5576c;"></i>
                                    <div>
                                        <div style="font-weight: 500;">Martes</div>
                                        <small style="color: #666;">Administrar horarios</small>
                                    </div>
                                </a>
                                <a class="dropdown-item-nav" href="miercoles/index.php">
                                    <i class="fas fa-calendar-day icon" style="color: #00f2fe;"></i>
                                    <div>
                                        <div style="font-weight: 500;">Miércoles</div>
                                        <small style="color: #666;">Administrar horarios</small>
                                    </div>
                                </a>
                                <a class="dropdown-item-nav" href="jueves/index.php">
                                    <i class="fas fa-calendar-day icon" style="color: #38f9d7;"></i>
                                    <div>
                                        <div style="font-weight: 500;">Jueves</div>
                                        <small style="color: #666;">Administrar horarios</small>
                                    </div>
                                </a>
                                <a class="dropdown-item-nav" href="viernes/index.php">
                                    <i class="fas fa-calendar-day icon" style="color: #fee140;"></i>
                                    <div>
                                        <div style="font-weight: 500;">Viernes</div>
                                        <small style="color: #666;">Administrar horarios</small>
                                    </div>
                                </a>
                                <a class="dropdown-item-nav" href="sabado/index.php">
                                    <i class="fas fa-calendar-day icon" style="color: #fed6e3;"></i>
                                    <div>
                                        <div style="font-weight: 500;">Sábado</div>
                                        <small style="color: #666;">Administrar horarios</small>
                                    </div>
                                </a>
                            </div>
                        </div>

                    <?php endif; ?>
                    
                    <!-- Botón adicional de navegación rápida -->
                    <a href="/blueadmin/horas.php" class="btn-nav">
                        <i class="fas fa-plus-circle"></i>
                        <span>Nuevo Registro</span>
                    </a>
                </div>
            </div>

            <!-- Sección de usuario -->
            <div class="col-lg-3 col-md-12">
                <div class="user-section">
                    <div class="user-info-card">
                        <div class="user-avatar">
                            <?php 
                            $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Usuario';
                            echo strtoupper(substr($username, 0, 1)); 
                            ?>
                        </div>
                        <div class="user-details">
                            <div class="user-name">
                                <?php echo htmlspecialchars($username); ?>
                            </div>
                            <div class="user-role">
                                <?php 
                                if (isset($_SESSION["id_tipo"])) {
                                    switch($_SESSION["id_tipo"]) {
                                        case 0: echo "Administrador"; break;
                                        case 1: echo "Maestra"; break;
                                        case 2: echo "Coordinador"; break;
                                        default: echo "Usuario"; break;
                                    }
                                } else {
                                    echo "Usuario";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <a href="/blueadmin/index.php" class="btn-logout" onclick="return confirm('¿Está seguro de que desea ir al inicio?')">
                        <i class="fas fa-home"></i>
                        <span>Inicio</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- JavaScript para funcionalidad del dropdown -->
<script>
function toggleDropdown(element) {
    const dropdown = element.parentElement;
    const chevron = element.querySelector('.fa-chevron-down');
    
    // Cerrar otros dropdowns
    document.querySelectorAll('.dropdown-nav.show').forEach(item => {
        if (item !== dropdown) {
            item.classList.remove('show');
            item.querySelector('.fa-chevron-down').style.transform = 'rotate(0deg)';
        }
    });
    
    // Toggle el dropdown actual
    dropdown.classList.toggle('show');
    
    // Animar el chevron
    if (dropdown.classList.contains('show')) {
        chevron.style.transform = 'rotate(180deg)';
    } else {
        chevron.style.transform = 'rotate(0deg)';
    }
}

// Cerrar dropdown al hacer click fuera
document.addEventListener('click', function(event) {
    if (!event.target.closest('.dropdown-nav')) {
        document.querySelectorAll('.dropdown-nav.show').forEach(dropdown => {
            dropdown.classList.remove('show');
            dropdown.querySelector('.fa-chevron-down').style.transform = 'rotate(0deg)';
        });
    }
});

// Marcar página activa
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.btn-nav');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });
});
</script>