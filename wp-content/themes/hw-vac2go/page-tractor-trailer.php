<?php if(!defined('ABSPATH'))exit; ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<?php get_template_part('template-parts/cat-head'); ?>
</head>
<body <?php body_class('hw-page'); ?>>
<?php get_template_part('template-parts/site-header'); ?>
<main id="hw-main">
  <section class="hw-cat-sec"><div class="hw-wrap">
    <h1 class="hw-cat-title">Tractors/Trailers</h1>
    <p class="hw-cat-intro">Vac2Go has Tractors and Two Box Roll Off Trailers to help you out with your job. Rent them together or on their own.</p>
  </div></section>
  <section class="hw-cat-gridsec"><div class="hw-wrap">
    <div class="hw-cat-grid">
      <div class="hw-cat-card">
        <a class="hw-cat-card-img" href="https://vac2go.com/tractors"><img src="/wp-content/uploads/2021/06/Tractor-1.jpg" alt="Tractors" loading="lazy" decoding="async" style="aspect-ratio:1500/1067"></a>
        <h3 class="hw-cat-card-title">Tractors</h3>
        <a class="hw-btn-red" href="https://vac2go.com/tractors">LEARN MORE</a>
      </div>
      <div class="hw-cat-card">
        <a class="hw-cat-card-img" href="https://vac2go.com/two-box-roll-off-trailers/"><img src="/wp-content/uploads/2021/06/Trailers.jpg" alt="Two Box Roll Off Trailers" loading="lazy" decoding="async" style="aspect-ratio:1500/1067"></a>
        <h3 class="hw-cat-card-title">Two Box Roll Off Trailers</h3>
        <a class="hw-btn-red" href="https://vac2go.com/two-box-roll-off-trailers/">LEARN MORE</a>
      </div>
    </div>
  </div></section>
</main>
<?php get_template_part('template-parts/site-footer'); wp_footer(); ?>
</body>
</html>
