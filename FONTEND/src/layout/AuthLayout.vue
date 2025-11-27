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
<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import authService from "../services/auth/authService.js";

const router = useRouter();

const passwordVisible = ref(false);
const email = ref("");
const password = ref("");
const isLoading = ref(false);
const errorMessage = ref("");

const togglePasswordVisibility = () => {
  passwordVisible.value = !passwordVisible.value;
};

const handleLogin = async () => {
  if (!email.value || !password.value) {
    errorMessage.value = "Vui lòng nhập đầy đủ thông tin";
    return;
  }

  isLoading.value = true;
  errorMessage.value = "";

  try {
    const result = await authService.login(email.value, password.value);

    if (result.success) {
      router.push({ name: `${result.role}.dashboard` });
    } else {
      errorMessage.value = result.error || "Đăng nhập thất bại";
    }
  } catch (error) {
    errorMessage.value = "Có lỗi xảy ra, vui lòng thử lại";
    console.error("Login error:", error);
  } finally {
    isLoading.value = false;
  }
};
</script>
