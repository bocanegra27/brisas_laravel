@extends('layouts.app')

@section('title', 'Brisas Gems - Inicio')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/index.css') }}" />
@endpush

@section('content')

  <!-- Hero / Carrusel -->
  <section class="carrusel">
    <div class="slide active" style="background-image:url('{{ asset('assets/img/index/imagen1.png') }}');"></div>
    <div class="slide" style="background-image:url('{{ asset('assets/img/index/imagen2.png') }}');"></div>
    <div class="slide" style="background-image:url('{{ asset('assets/img/index/imagen3.png') }}');"></div>

    <div class="cta-hero text-center">
      <h1>Diseña tu joya soñada</h1>
      <p>Personaliza paso a paso con Brisas Gems</p>
      <a class="btn btn-light" href="{{ url('/personalizar') }}">Comenzar personalización</a>
    </div>
  </section>

  <!-- Bienvenida -->
  <section class="info-section py-5 text-center">
    <h2 class="mb-3">Bienvenido a Brisas Gems</h2>
    <p>Donde tus ideas cobran vida en joyas exclusivas hechas a tu medida.</p>
  </section>

  <!-- Personalización -->
  <section class="modulo-section container py-5">
    <div class="row align-items-center">
      <div class="col-md-6">
        <img class="img-fluid rounded shadow"
             src="{{ asset('assets/img/index/proceso1.jpg') }}"
             alt="Personalización de joyas">
      </div>
      <div class="col-md-6">
        <h3>Personaliza tu anillo</h3>
        <p>Elige gema, forma, material y talla en tiempo real.</p>
        <a class="btn btn-primary" href="{{ url('/personalizar') }}">Ir al configurador</a>
      </div>
    </div>
  </section>

  <!-- Inspiración -->
  <section class="modulo-section bg-light py-5 text-center">
    <h3>¿Necesitas inspiración?</h3>
    <p>Explora diseños previos y encuentra ideas para tu joya.</p>
    <a class="btn btn-outline-dark" href="{{ url('/inspiracion') }}">Ver catálogo</a>
  </section>

  <!-- Pedidos -->
  <section class="modulo-section container py-5">
    <div class="row align-items-center">
      <div class="col-md-6 order-md-2">
        <img class="img-fluid rounded shadow"
             src="{{ asset('assets/img/index/proceso2.jpg') }}"
             alt="Seguimiento de pedidos">
      </div>
      <div class="col-md-6">
        <h3>Sigue tu pedido</h3>
        <p>Revisa el estado desde la confirmación hasta la entrega final.</p>
        <a class="btn btn-success" href="{{ url('/usuario/mis-pedidos') }}">Mis pedidos</a>
      </div>
    </div>
  </section>

  <!-- Contacto -->
  <section class="modulo-section bg-light py-5 text-center">
    <h3>¿Necesitas ayuda?</h3>
    <p>Escríbenos para resolver dudas o recibir asesoría personalizada.</p>
    <a class="btn btn-outline-primary" href="{{ url('/contacto') }}">Formulario de contacto</a>
  </section>
@endsection

@push('scripts')
<script>
  // Carrusel simple
  const slides = document.querySelectorAll(".slide");
  let idx = 0;
  setInterval(() => {
    slides[idx].classList.remove("active");
    idx = (idx + 1) % slides.length;
    slides[idx].classList.add("active");
  }, 4000);
</script>
@endpush