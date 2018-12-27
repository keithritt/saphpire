<?


print '

<img class="bg" src="http://officialloop.com/uploads/dev/corner_bar_com/back_01.jpg">

<style>
img.bg {
  /* Set rules to fill background */
  min-height: 100%;
  min-width: 1024px;

  /* Set up proportionate scaling */
  width: 100%;
  height: auto;

  /* Set up positioning */
  position: fixed;
  top: 0;
  left: 0;

  z-index: -1;
  x-webkit-filter: grayscale(1);
  fxilter: grayscale(1);
}

@media screen and (max-width: 1024px) { /* Specific to this particular image */
  img.bg {
    left: 50%;
    margin-left: -512px;   /* 50% */
  }
}</style>';