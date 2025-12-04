<template>
  <div
    class="w-screen h-screen bg-cover bg-center bg-no-repeat bg-[url('./assets/auth_bg.jpg')] flex items-center justify-center"
  >
    <div class="max-w-3xl w-full h-150 rounded-xl shadow-xl grid grid-cols-2">
      <div
        class="bg-white rounded-l-2xl flex flex-col items-center justify-center p-10 space-y-6 w-full max-w-md"
      >
        <h1 class="text-3xl font-bold text-center">Đăng nhập</h1>
        <p class="text-sm text-gray-500 text-center">
          Xin chào, đến với hệ thống
        </p>

        <form
          @submit.prevent="handleLogin"
          class="flex flex-col w-full space-y-4 relative"
        >
          <div
            v-if="errorMessage"
            class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"
          >
            {{ errorMessage }}
          </div>

          <input
            v-model="email"
            type="email"
            placeholder="Email"
            class="w-full px-4 py-3 rounded-lg bg-gray-100 border border-gray-300 placeholder-gray-500 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
            :disabled="isLoading"
          />

          <div class="relative w-full">
            <input
              v-model="password"
              :type="passwordVisible ? 'text' : 'password'"
              placeholder="Mật khẩu"
              class="w-full px-4 py-3 rounded-lg bg-gray-100 border border-gray-300 placeholder-gray-500 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 pr-10"
              :disabled="isLoading"
            />

            <button
              type="button"
              @click="togglePasswordVisibility"
              class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"
              :disabled="isLoading"
            >
              <font-awesome-icon
                :icon="passwordVisible ? 'eye-slash' : 'eye'"
              />
            </button>
          </div>
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full bg-indigo-500 text-white py-3 rounded-lg font-medium hover:bg-indigo-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="isLoading">Đang đăng nhập...</span>
            <span v-else>Đăng nhập</span>
          </button>
        </form>
      </div>

      <div
        class="bg-blue-500 rounded-r-2xl border-black flex items-center justify-center"
      >
        <img
          src="https://elearning.vinhuni.edu.vn/pluginfile.php/1/theme_klass/logo/1736161506/logo.png"
          alt=""
          class="h-40 w-40"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { useRouter } from "vue-router";
import authService from "../services/auth/authService.js";

export default {
  name: "Login",
  data() {
    return {
      passwordVisible: false,
      email: "",
      password: "",
      isLoading: false,
      errorMessage: "",
    };
  },
  methods: {
    togglePasswordVisibility() {
      this.passwordVisible = !this.passwordVisible;
    },
    async handleLogin() {
      if (!this.email || !this.password) {
        this.errorMessage = "Vui lòng nhập đầy đủ thông tin";
        return;
      }

      this.isLoading = true;
      this.errorMessage = "";

      try {
        const result = await authService.login(this.email, this.password);

        if (result.success) {
          this.$router.push({ name: `${result.role}.dashboard` });
        } else {
          this.errorMessage = result.error || "Đăng nhập thất bại";
        }
      } catch (error) {
        this.errorMessage = "Có lỗi xảy ra, vui lòng thử lại";
        console.error("Login error:", error);
      } finally {
        this.isLoading = false;
      }
    },
  },
  setup() {
    const router = useRouter();
    return { router };
  },
};
</script>
