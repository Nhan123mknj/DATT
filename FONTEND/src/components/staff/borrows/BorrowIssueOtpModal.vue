<template>
  <Modal :show="show" title="Xác thực xuất kho" @close="$emit('close')">
    <div class="space-y-4">
      <p class="text-sm text-gray-500">
        Vui lòng gửi mã OTP đến email của người mượn và nhập mã xác thực để hoàn
        tất xuất kho.
      </p>

      <div v-if="step === 1" class="flex justify-center py-4">
        <button
          type="button"
          class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          :disabled="loading"
          @click="sendOtp"
        >
          <span v-if="loading">Đang gửi...</span>
          <span v-else>Gửi mã OTP đến Email</span>
        </button>
      </div>

      <div v-else class="space-y-4">
        <div class="bg-blue-50 p-3 rounded-md">
          <p class="text-sm text-blue-700">
            Mã OTP đã được gửi đến: <strong>{{ email }}</strong>
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Nhập mã OTP (6 số)
          </label>
          <div class="flex justify-center">
            <v-otp-input
              ref="otpInput"
              v-model:value="otp"
              input-classes="otp-input"
              separator="-"
              :num-inputs="6"
              :should-auto-focus="true"
              :is-input-num="true"
              @on-complete="handleOnComplete"
              @on-change="handleOnChange"
            />
          </div>
          <p v-if="error" class="mt-2 text-sm text-red-600 text-center">
            {{ error }}
          </p>
        </div>

        <div class="flex justify-between items-center">
          <button
            type="button"
            class="text-sm text-indigo-600 hover:text-indigo-500"
            @click="sendOtp"
            :disabled="loading"
          >
            Gửi lại mã?
          </button>
        </div>
      </div>
    </div>
    <template #footer>
      <button
        type="button"
        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
        :disabled="loading || step === 1 || otp.length !== 6"
        @click="submitOtp"
      >
        <span v-if="loading">Đang xử lý...</span>
        <span v-else>Xác nhận xuất kho</span>
      </button>
      <button
        type="button"
        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
        @click="$emit('close')"
      >
        Hủy
      </button>
    </template>
  </Modal>
</template>

<script>
import { ref, watch } from "vue";
import Modal from "../../Modal.vue";
import { staffBorrowService } from "../../../services/staff/staffBorrowService";
import { useToast } from "vue-toastification";
import VOtpInput from "vue3-otp-input";

export default {
  components: { Modal, VOtpInput },
  props: {
    show: Boolean,
    borrow: Object,
  },
  emits: ["close", "success"],
  setup(props, { emit }) {
    const toast = useToast();
    const step = ref(1);
    const loading = ref(false);
    const otp = ref("");
    const email = ref("");
    const error = ref("");
    const otpInput = ref(null);

    watch(
      () => props.show,
      (val) => {
        if (val) {
          step.value = 1;
          otp.value = "";
          error.value = "";
          email.value = "";
        }
      }
    );

    const handleOnComplete = (value) => {
      otp.value = value;
      submitOtp();
    };

    const handleOnChange = (value) => {
      otp.value = value;
    };

    const sendOtp = async () => {
      if (!props.borrow?.id) return;
      loading.value = true;
      error.value = "";
      try {
        const res = await staffBorrowService.sendOtp(props.borrow.id);
        email.value = res.data.email;
        step.value = 2;
        toast.success("Đã gửi mã OTP thành công");
      } catch (err) {
        toast.error(err.response?.data?.message || "Lỗi khi gửi OTP");
      } finally {
        loading.value = false;
      }
    };

    const submitOtp = async () => {
      if (otp.value.length !== 6) {
        error.value = "Vui lòng nhập đủ 6 số";
        return;
      }
      loading.value = true;
      error.value = "";
      try {
        await staffBorrowService.issue(props.borrow.id, otp.value);
        toast.success("Xuất kho thành công");
        emit("success");
        emit("close");
      } catch (err) {
        error.value = err.response?.data?.message || "Mã OTP không đúng";
        if (otpInput.value) {
          otpInput.value.clearInput();
        }
      } finally {
        loading.value = false;
      }
    };

    return {
      step,
      loading,
      otp,
      email,
      error,
      sendOtp,
      submitOtp,
      handleOnComplete,
      handleOnChange,
      otpInput,
    };
  },
};
</script>

<style>
.otp-input {
  width: 40px;
  height: 40px;
  padding: 5px;
  margin: 0 5px;
  font-size: 20px;
  border-radius: 4px;
  border: 1px solid rgba(0, 0, 0, 0.3);
  text-align: center;
}
.otp-input:focus {
  outline: none;
  border: 2px solid #4f46e5;
}
.otp-input.error {
  border: 1px solid red !important;
}
.otp-input::-webkit-inner-spin-button,
.otp-input::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>
