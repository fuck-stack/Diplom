<style>
& when not (@fullScreen) {
  padding-top: (@paddingTop * 1rem);
  padding-bottom: (@paddingBottom * 1rem);
}
& when (@bg-type = "color") {
  background-color: if(@transparentBg, transparent, @bg-value);
}
& when (@bg-type = 'image') {
  background-image: url(@bg-value);
}
.mbr-fallback-image.disabled {
  display: none;
}
.mbr-fallback-image {
  display: block;
  background-size: cover;
  background-position: center center;
  width: 100%;
  height: 100%;
  position: absolute;
  top: 0;
  & when (@bg-type = 'video') {
    background-image: url(@fallBackImage);
  }
}
& when (@fullWidth) {
  .container-fluid {
    padding: 0 56px;
    @media (max-width: 992px) {
      padding: 0 26px;
    }
  }
}
.container {
  @media (max-width: 992px) {
    padding: 0 26px;
  }
}
.row {
  justify-content: center;
}
.items-wrapper {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 45px;
  @media (max-width: 992px) {
    display: block;
  }
  .item {
    @media (max-width: 992px) {
      margin-bottom: 40px;
    }
    &:focus, &:hover {
      .item-wrapper .item-img img {
        transform: scale(1);
      }
    }
    .item-wrapper {
      display: flex;
      @media (max-width: 992px) {
        display: block;
      }
      .item-content {
        width: 50%;
        padding-right: 20px;
        @media (max-width: 992px) {
          width: 100%;
          margin-bottom: 20px;
          padding-right: 0;
        }
        .item-title {
          margin-bottom: 5px;
        }
        .item-desc {
          margin-bottom: 0;
          padding-bottom: 5px;
          border-bottom: 1px solid @border;
        }
      }
      .item-img {
        width: 50%;
        border-radius: 20px;
        overflow: hidden;
        @media (max-width: 992px) {
          width: 100%;
        }
        img {
          height: 340px;
          width: 100%;
          object-fit: cover;
          transform: scale(1.15);
          transition: all 0.3s ease-in-out;
        }
      }
    }
  }
}
.mbr-section-btn {
  margin-top: 50px;
  @media (max-width: 992px) {
    margin-top: 0;
  }
  text-align: center;
}
.item-title {
  color: #000000;
}
.item-desc {
  color: #000000;
}
.item-title, .mbr-section-btn {
  color: #000000;
}

</style>
<div id="carouselExampleCaptions" class="carousel slide" data-ride="carousel">
  <ol class="carousel-indicators">
    <li data-target="#carouselExampleCaptions" data-slide-to="0" class="active"></li>
    <li data-target="#carouselExampleCaptions" data-slide-to="1"></li>
    <li data-target="#carouselExampleCaptions" data-slide-to="2"></li>
  </ol>
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="img/assassin_s_creed_carusel.png" class="d-block w-100" alt="...">
      <div class="carousel-caption d-none d-md-block">
        <h5>assassin_s_creed</h5>
        <p>Новинки</p>
      </div>
    </div>
    <div class="carousel-item">
      <img src="img/baldur_hero.png" class="d-block w-100" alt="...">
      <div class="carousel-caption d-none d-md-block">
        <h5>baldur_hero</h5>
        <p>Новинки</p>
      </div>
    </div>
    <div class="carousel-item">
      <img src="img/inzoi-hero.png" class="d-block w-100" alt="...">
      <div class="carousel-caption d-none d-md-block">
        <h5>inzoi-hero</h5>
        <p>Новинки</p>
      </div>
    </div>
  </div>
  <div class="carousel-item">
      <img src="img/Split Fiction_carusel.png" class="d-block w-100" alt="...">
      <div class="carousel-caption d-none d-md-block">
        <h5>Split Fiction</h5>
        <p>Новинки</p>
      </div>
    </div>
    <a class="carousel-control-prev" href="#carouselExampleCaptions" role="button" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="carousel-control-next" href="#carouselExampleCaptions" role="button" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a>
  </div>

</div>
    </div>