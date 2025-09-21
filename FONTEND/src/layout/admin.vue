<template>
  <div class="wrapper">
    <Header />

    <div class="container-fluid">
      <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar bg-dark min-vh-100">
          <div class="position-sticky">
            <ul class="nav flex-column pt-3">
              <li class="nav-item">
                <router-link to="/admin/dashboard" class="nav-link text-white">
                  <i class="bi bi-speedometer2 me-2"></i>
                  Dashboard
                </router-link>
              </li>
              <li class="nav-item">
                <router-link to="/admin/users" class="nav-link text-white">
                  <i class="bi bi-people me-2"></i>
                  Users
                </router-link>
              </li>
              <li class="nav-item">
                <router-link to="/admin/movies" class="nav-link text-white">
                  <i class="bi bi-film me-2"></i>
                  Movies
                </router-link>
              </li>
            </ul>
          </div>
        </div>

        <!-- Main Content -->
        <!-- <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-3">
          <router-view></router-view>
        </div> -->
      </div>
    </div>
  </div>
</template>

<script>
import Header from "../components/Header.vue";

export default {
  components: {
    Header,
  },
};
</script>

<style scoped>
.sidebar {
  position: fixed;
  top: 56px; /* Adjust based on your header height */
  left: 0;
  z-index: 100;
  padding: 0;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.nav-link {
  padding: 0.8rem 1rem;
  transition: all 0.3s;
}

.nav-link:hover {
  background-color: rgba(255, 255, 255, 0.1);
}
</style>
