{{-- ============================================
     FOOTER MINIMALISTA - BRISAS GEMS
     Diseño elegante que complementa el header
     ============================================ --}}

<footer class="footer-minimal">
    <div class="footer-minimal__container">
        
        {{-- Grid de columnas --}}
        <div class="footer-minimal__grid">
            
            {{-- COLUMNA 1: Sobre Brisas Gems --}}
            <div class="footer-minimal__column">
                {{-- Logo opcional --}}
                <a href="{{ url('/') }}" class="footer-minimal__logo">
                    <img src="{{ asset('assets/img/logo/logo_120.png') }}" 
                        alt="Brisas Gems Logo" 
                        class="footer-minimal__logo-img">
                    <span class="footer-minimal__logo-text">Brisas Gems</span>
                </a>
                
                <p class="footer-minimal__text">
                    Joyería fina y personalizada con los más altos estándares de calidad. 
                    Creamos piezas únicas que cuentan historias.
                </p>
                
                {{-- Redes Sociales --}}
                <div class="footer-minimal__social">
                    <a href="#" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="footer-minimal__social-link"
                       aria-label="WhatsApp Brisas Gems">
                        <img src="{{ asset('assets/img/icons/icono-whatsApp.png') }}" 
                             alt="WhatsApp" 
                             class="footer-minimal__social-icon">
                    </a>
                    <a href="#" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="footer-minimal__social-link"
                       aria-label="Instagram Brisas Gems">
                        <img src="{{ asset('assets/img/icons/icono instagram.png') }}" 
                             alt="Instagram" 
                             class="footer-minimal__social-icon">
                    </a>
                    <a href="#" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="footer-minimal__social-link"
                       aria-label="Facebook Brisas Gems">
                        <img src="{{ asset('assets/img/icons/icono-facebook.png') }}" 
                             alt="Facebook" 
                             class="footer-minimal__social-icon">
                    </a>
                </div>
            </div>

            {{-- COLUMNA 2: Contacto --}}
            <div class="footer-minimal__column">
                <h4 class="footer-minimal__title">Contacto</h4>
                
                <div class="footer-minimal__contact-item">
                    <span class="footer-minimal__icon"></span>
                    <span>Av Jiménez #5-43, Emerald Trade Center, Bogotá</span>
                </div>
                
                <div class="footer-minimal__contact-item">
                    <span class="footer-minimal__icon"></span>
                    <a href="tel:+5760176543312" 
                       style="color: inherit; text-decoration: none; transition: color 0.3s ease;"
                       onmouseover="this.style.color='#009688'" 
                       onmouseout="this.style.color='inherit'">
                        +57 6017654312
                    </a>
                </div>
                
                <div class="footer-minimal__contact-item">
                    <span class="footer-minimal__icon"></span>
                    <a href="mailto:info@brisasgem.com" 
                       style="color: inherit; text-decoration: none; transition: color 0.3s ease;"
                       onmouseover="this.style.color='#009688'" 
                       onmouseout="this.style.color='inherit'">
                        info@brisasgem.com
                    </a>
                </div>
            </div>

            {{-- COLUMNA 3: Enlaces Rápidos --}}
            <div class="footer-minimal__column">
                <h4 class="footer-minimal__title">Enlaces Rápidos</h4>
                
                <nav aria-label="Enlaces rápidos del footer">
                    <ul class="footer-minimal__nav">
                        <li class="footer-minimal__nav-item">
                            <a href="{{ url('/') }}" class="footer-minimal__nav-link">
                                Inicio
                            </a>
                        </li>
                        <li class="footer-minimal__nav-item">
                            <a href="{{ url('/inspiracion') }}" class="footer-minimal__nav-link">
                                Inspiración
                            </a>
                        </li>
                        <li class="footer-minimal__nav-item">
                            <a href="{{ url('/personalizar') }}" class="footer-minimal__nav-link">
                                Personalización
                            </a>
                        </li>
                        <li class="footer-minimal__nav-item">
                            <a href="{{ url('/contacto') }}" class="footer-minimal__nav-link">
                                Contacto
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

        </div>

        {{-- Divider --}}
        <div class="footer-minimal__divider"></div>

        {{-- Bottom: Derechos y Créditos --}}
        <div class="footer-minimal__bottom">
            <p class="footer-minimal__copyright">
                © {{ date('Y') }} Brisas Gems - Todos los derechos reservados
            </p>
            <p class="footer-minimal__credits">
                Desarrollado con 💎 por <a href="https://www.sena.edu.co/" target="_blank" rel="noopener">SENA CEET</a> - Ficha 2996176 ADSO
            </p>
        </div>

    </div>
</footer>