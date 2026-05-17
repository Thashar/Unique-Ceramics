</main>

<!-- FOOTER -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">

      <!-- Brand -->
      <div class="footer-col">
        <div class="footer-logo">
          <img src="<?= BASE_PATH ?>/assets/images/logo.jpg" alt="<?= SITE_NAME ?>" onerror="this.style.display='none'">
          <span class="footer-logo-name"><?= SITE_NAME ?></span>
        </div>
        <p class="footer-tagline"><?= t('footer.tagline') ?></p>
        <div class="footer-social">
          <?php if (SITE_INSTAGRAM): ?>
            <a href="<?= SITE_INSTAGRAM ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <?php endif; ?>
          <?php if (SITE_FACEBOOK): ?>
            <a href="<?= SITE_FACEBOOK ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <?php endif; ?>
          <a href="mailto:<?= SITE_EMAIL ?>" aria-label="Email"><i class="fas fa-envelope"></i></a>
        </div>
      </div>

      <!-- Shop links -->
      <div class="footer-col">
        <h4><?= t('footer.shop') ?></h4>
        <ul>
          <?php foreach (get_categories() as $cat): ?>
            <li><a href="<?= url('shop.php?cat=' . $cat['slug']) ?>"><?= category_name($cat) ?></a></li>
          <?php endforeach; ?>
          <li><a href="<?= url('custom-order.php') ?>"><?= t('nav.custom') ?></a></li>
        </ul>
      </div>

      <!-- Info links -->
      <div class="footer-col">
        <h4><?= t('footer.info') ?></h4>
        <ul>
          <li><a href="<?= url('about.php') ?>"><?= t('nav.about') ?></a></li>
          <li><a href="<?= url('workshops.php') ?>"><?= t('nav.workshops') ?></a></li>
          <li><a href="<?= url('contact.php') ?>"><?= t('nav.contact') ?></a></li>
          <li><a href="<?= url('contact.php') ?>"><?= t('footer.terms') ?></a></li>
          <li><a href="<?= url('contact.php') ?>"><?= t('footer.privacy') ?></a></li>
        </ul>
      </div>

      <!-- Contact + Newsletter -->
      <div class="footer-col">
        <h4><?= t('footer.contact') ?></h4>
        <div class="footer-contact-row">
          <i class="fas fa-phone-alt"></i>
          <a href="tel:<?= str_replace(' ', '', SITE_PHONE) ?>"><?= SITE_PHONE ?></a>
        </div>
        <div class="footer-contact-row">
          <i class="fas fa-envelope"></i>
          <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>
        </div>
        <div class="footer-contact-row">
          <i class="fab fa-instagram"></i>
          <a href="<?= SITE_INSTAGRAM ?>" target="_blank" rel="noopener">@unique.ceramics</a>
        </div>
        <h4 style="margin-top:1.2rem"><?= t('footer.newsletter_title') ?></h4>
        <form class="newsletter-form" action="#" method="post">
          <input type="email" placeholder="<?= h(t('footer.newsletter_ph')) ?>" name="email" autocomplete="email">
          <button type="submit" class="btn btn-primary btn-sm"><?= t('footer.newsletter_btn') ?></button>
        </form>
      </div>

    </div><!-- /footer-grid -->

    <!-- Footer bottom -->
    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. <?= t('footer.rights') ?>.</span>
      <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
        <small><?= t('footer.shipping_info') ?></small>
        <span>|</span>
        <small><?= t('footer.payment_methods') ?>: <strong>Przelew · PayU · Przelewy24 · Stripe</strong></small>
      </div>
      <div class="footer-links">
        <a href="<?= url('contact.php') ?>"><?= t('footer.terms') ?></a>
        <a href="<?= url('contact.php') ?>"><?= t('footer.privacy') ?></a>
      </div>
    </div>

  </div><!-- /container -->
</footer>

<!-- Cookie notice -->
<div class="cookie-notice" id="cookieNotice">
  <p><?= current_lang() === 'pl'
    ? 'Ta strona używa plików cookie do poprawnego działania koszyka i analizy ruchu.'
    : 'This site uses cookies for the shopping cart and traffic analysis.' ?>
  </p>
  <button class="btn btn-primary btn-sm cookie-accept"><?= current_lang() === 'pl' ? 'Rozumiem' : 'Accept' ?></button>
</div>

<script src="<?= BASE_PATH ?>/assets/js/main.js"></script>
</body>
</html>
