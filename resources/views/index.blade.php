@extends('layouts.app')

@section('title', 'Brisas Gems - Inicio')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/index.css') }}" />
@endpush

@section('content')

@if(session('success'))
<div class="container mt-4">
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
@endif

@if(session('error'))
<div class="container mt-4">
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
@endif


  <!-- ===== PARTE 2: VIDEO HERO ===== -->
  <section class="hero-video-section">
    <video autoplay muted loop playsinline class="hero-video">
      <source src="{{ asset('assets/video/hero.mp4') }}" type="video/mp4">
    </video>
  </section>

  <!-- ===== PARTE 4: TÍTULO Y SUBTÍTULO ===== -->
  <section class="intro-text-section text-center">
    <h2 class="intro-title">BIENVENIDO A BRISAS GEMS</h2>
    <p class="intro-subtitle">Donde tus ideas cobran vida en joyas exclusivas hechas a tu medida.</p>
  </section>

  <!-- ===== PARTE 5: 3 TARJETAS DE INFORMACIÓN ===== -->
  <section class="cards-section container-fluid">
    <div class="row justify-content-center g-4">

      <div class="col-md-4 col-sm-6">
        <div class="info-card">
          <h4 class="info-card-title">Diseño Personalizado</h4>
          <p class="info-card-text">Diseña tu joya desde cero eligiendo cada detalle: el tipo de metal, la gema, la forma, el grabado y la talla. Nuestro configurador en tiempo real te permite visualizar el resultado antes de confirmar tu pedido, garantizando que la pieza sea exactamente como la imaginaste.</p>
        </div>
      </div>

      <div class="col-md-4 col-sm-6">
        <div class="info-card">
          <h4 class="info-card-title">Calidad Garantizada</h4>
          <p class="info-card-text">Cada joya que sale de nuestro taller es elaborada por artesanos colombianos con décadas de experiencia. Usamos exclusivamente materiales certificados: oro de 18k, plata 925 y gemas naturales con trazabilidad garantizada. Tu satisfacción respaldada por nuestra garantía de por vida.</p>
        </div>
      </div>

      <div class="col-md-4 col-sm-6">
        <div class="info-card">
          <h4 class="info-card-title">Seguimiento en Tiempo Real</h4>
          <p class="info-card-text">Desde el momento en que confirmas tu pedido hasta que llega a tus manos, podrás monitorear cada etapa del proceso: diseño, fabricación, control de calidad y envío. Recibirás notificaciones en cada paso para que siempre estés informado sobre el estado de tu joya.</p>
        </div>
      </div>

    </div>
  </section>

  <!-- ===== PARTE 6: SECCIÓN CONTACTO (FONDO GRIS) ===== -->
  <section class="contact-section">
    <div class="container text-center">
      <h2 class="contact-title">¿LISTO PARA CREAR TU JOYA?</h2>
      <p class="contact-subtitle">Nuestro equipo está aquí para guiarte en cada paso del proceso.</p>

      <div class="row justify-content-center g-4 contact-photos-row">
        <div class="col-md-3 col-sm-6">
          <div class="contact-photo-wrap">
            <img src="{{ asset('assets/img/index/proceso1.png') }}" alt="Asesoría personalizada" class="contact-photo">
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="contact-photo-wrap">
            <img src="{{ asset('assets/img/index/proceso2.png') }}" alt="Nuestro equipo" class="contact-photo">
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="contact-photo-wrap">
            <img src="{{ asset('assets/img/index/proceso3.png') }}" alt="Diseños exclusivos" class="contact-photo">
          </div>
        </div>
         <div class="col-md-3 col-sm-6">
          <div class="contact-photo-wrap">
            <img src="{{ asset('assets/img/index/proceso4.png') }}" alt="Diseños exclusivos" class="contact-photo">
          </div>
        </div>
        
      </div>

      
  </section>

  <!-- ===== PARTE 7: SECCIÓN PERSONALIZACIÓN (FONDO BLANCO) ===== -->
  <section class="personalize-section container-fluid">
    <h2 class="personalize-title text-center">¡CREA LA JOYA QUE SIEMPRE IMAGINASTE!</h2>

    <!-- Fila 1: Imagen izquierda, contenedor info derecha -->
    <div class="row align-items-stretch g-5 personalize-row">
      <div class="col-md-7 personalize-img-wrap">
        <img src="{{ asset('assets/img/index/personaliza1.png') }}"
             alt="Proceso de personalización"
             class="personalize-img">
      </div>
      <div class="col-md-5">
        <div class="personalize-info-box">
          <h4>Exclusividad que te representa</h4>
          <p>Nuestras joyas están diseñadas para personas auténticas que buscan algo más que un accesorio. Cada pieza conserva un carácter único, con detalles cuidadosamente trabajados que resaltan tu estilo sin importar quién seas. Porque la verdadera exclusividad no excluye, celebra la identidad de cada persona.</p>
        </div>
      </div>
    </div>

    <!-- Fila 2: Contenedor info izquierda, imagen derecha -->
    <div class="row align-items-stretch g-5 personalize-row">
      <div class="col-md-5">
        <div class="personalize-info-box">
          <h4>Más que una joya, una huella.</h4>
          <p>Hay detalles que no se repiten, como las historias que cada persona lleva consigo. Nuestras piezas están pensadas para convertirse en esa huella que te acompaña, marcando diferencia con sutileza y carácter.
Porque lo verdaderamente especial no sigue tendencias, crea su propio camino.</p>
        </div>
      </div>
      <div class="col-md-7 personalize-img-wrap">
        <img src="{{ asset('assets/img/index/personaliza2.png') }}"
             alt="Materiales de calidad"
             class="personalize-img">
      </div>
    </div>

  </section>

  <!-- ===== PARTE 8: CTA + CARRUSEL + HISTORIA ===== -->
  <section class="carousel-section text-center">
<h2 class="carousel-cta-title">UN PROCESO, UNA JOYA, UNA TRADICIÓN...</h2>
    

    <!-- Carrusel centrado al 50% -->
    <div class="carousel-wrapper">
      <div class="carrusel" id="mainCarousel">
<div class="slide active" style="background-image: url('{{ asset('assets/img/index/carrusel1.png') }}');"></div>
<div class="slide" style="background-image: url('{{ asset('assets/img/index/carrusel2.png') }}');"></div>
<div class="slide" style="background-image: url('{{ asset('assets/img/index/carrusel3.png') }}');"></div>
      </div>
    </div>

    <!-- Historia joyería colombiana -->
    <div class="history-text">
      <p>
        Colombia tiene una tradición joyera que se remonta a miles de años, cuando las culturas precolombinas
        dominaban el arte del oro como ninguna otra civilización. Hoy, Brisas Gems honra esa herencia ancestral
        fusionando técnicas artesanales de generaciones con el diseño contemporáneo. Cada joya que creamos
        es un homenaje a la riqueza cultural colombiana y a la maestría de sus artesanos.
      </p>
    </div>

  </section>

@endsection

@push('scripts')
<script>
  // Carrusel simple
  const slides = document.querySelectorAll("#mainCarousel .slide");
  let idx = 0;
  setInterval(() => {
    slides[idx].classList.remove("active");
    idx = (idx + 1) % slides.length;
    slides[idx].classList.add("active");
  }, 4000);
</script>
@endpush