# Flowbite Setup - Cấu Hình Chính Thức

## ✅ Đã Hoàn Tất

### 1. **style.css** - Import Flowbite đúng cách

```css
@import "tailwindcss";

/* Flowbite default theme */
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap");
@import "flowbite/src/themes/default";

@plugin "flowbite/plugin";
@source "../node_modules/flowbite";
```

### 2. **main.js** - Khởi tạo Flowbite

```javascript
import { initFlowbite } from "flowbite";

// ... setup app ...

app.mount("#app");

// Initialize Flowbite components
initFlowbite();
```

### 3. **tailwind.config.js** - Config Tailwind (đã sẵn)

```javascript
plugins: [require("flowbite/plugin")];
```

---

## 🚀 Cách Sử Dụng Flowbite Components

### Phương 1: Sử dụng Data Attributes (Đơn giản)

Components như Modal, Dropdown, Tabs hoạt động tự động với data attributes:

```vue
<template>
  <!-- Button mở Modal -->
  <button
    data-modal-target="myModal"
    data-modal-toggle="myModal"
    class="text-white bg-blue-700 hover:bg-blue-800 px-5 py-2.5 rounded-lg"
  >
    Open Modal
  </button>

  <!-- Modal -->
  <div
    id="myModal"
    tabindex="-1"
    aria-hidden="true"
    class="hidden fixed top-0 left-0 right-0 z-50 w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 max-h-full"
  >
    <div class="relative w-full max-w-md max-h-full">
      <div class="relative bg-white rounded-lg shadow">
        <!-- Modal Header -->
        <div
          class="flex items-center justify-between p-4 md:p-5 border-b rounded-t"
        >
          <h3 class="text-xl font-semibold text-gray-900">Modal Title</h3>
          <button
            type="button"
            data-modal-hide="myModal"
            class="text-gray-400 bg-transparent hover:text-gray-900 text-sm w-8 h-8"
          >
            ✕
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-4 md:p-5 space-y-4">
          <p class="text-base text-gray-500">Modal content here...</p>
        </div>

        <!-- Modal Footer -->
        <div class="flex items-center p-4 md:p-5 border-t rounded-b gap-3">
          <button
            data-modal-hide="myModal"
            type="button"
            class="text-white bg-blue-700 hover:bg-blue-800 px-5 py-2.5 rounded-lg"
          >
            Confirm
          </button>
          <button
            data-modal-hide="myModal"
            type="button"
            class="text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 px-5 py-2.5 rounded-lg"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Không cần code gì - hoạt động tự động với initFlowbite()
</script>
```

### Phương 2: Sử dụng JavaScript API (Nâng cao)

```vue
<template>
  <button id="openBtn" class="text-white bg-blue-700 px-5 py-2.5 rounded-lg">
    Open Modal
  </button>

  <div
    id="myModal"
    tabindex="-1"
    aria-hidden="true"
    class="hidden fixed top-0 left-0 right-0 z-50 w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 max-h-full"
  >
    <div class="relative w-full max-w-md max-h-full">
      <!-- Modal content -->
    </div>
  </div>
</template>

<script setup>
import { onMounted } from "vue";
import { Modal } from "flowbite";

onMounted(() => {
  const modalElement = document.getElementById("myModal");
  const modal = new Modal(modalElement);

  document.getElementById("openBtn").addEventListener("click", () => {
    modal.show();
  });
});
</script>
```

---

## 📦 Các Component Phổ Biến

### 1. **Button**

```vue
<!-- Primary -->
<button
  class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5"
>
  Button
</button>

<!-- Secondary -->
<button
  class="text-gray-900 bg-white border border-gray-300 hover:bg-gray-100 px-5 py-2.5 rounded-lg"
>
  Button
</button>

<!-- Danger -->
<button class="text-white bg-red-700 hover:bg-red-800 px-5 py-2.5 rounded-lg">
  Delete
</button>
```

### 2. **Input**

```vue
<input
  type="email"
  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
  placeholder="name@example.com"
/>
```

### 3. **Select**

```vue
<select
  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
>
  <option>Choose option</option>
  <option value="1">Option 1</option>
  <option value="2">Option 2</option>
</select>
```

### 4. **Checkbox**

```vue
<div class="flex items-center">
  <input 
    id="checkbox" 
    type="checkbox" 
    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
  />
  <label for="checkbox" class="ml-2 text-sm font-medium text-gray-900">
    Agree to terms
  </label>
</div>
```

### 5. **Alert**

```vue
<!-- Success -->
<div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50">
  <span class="font-medium">Success!</span> Message here
</div>

<!-- Error -->
<div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50">
  <span class="font-medium">Error!</span> Message here
</div>

<!-- Info -->
<div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50">
  <span class="font-medium">Info!</span> Message here
</div>
```

### 6. **Badge**

```vue
<span
  class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full"
>
  Badge
</span>
```

### 7. **Dropdown**

```vue
<div class="relative inline-block text-left">
  <button
    id="dropdownBtn"
    data-dropdown-toggle="dropdown"
    class="text-white bg-blue-700 hover:bg-blue-800 px-5 py-2.5 rounded-lg"
  >
    Menu
  </button>

  <div id="dropdown" data-dropdown-placement="bottom" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44">
    <ul class="py-2 text-sm text-gray-700">
      <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Option 1</a></li>
      <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Option 2</a></li>
      <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Option 3</a></li>
    </ul>
  </div>
</div>
```

### 8. **Table**

```vue
<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
  <table class="w-full text-sm text-left text-gray-500">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
      <tr>
        <th scope="col" class="px-6 py-3">Name</th>
        <th scope="col" class="px-6 py-3">Email</th>
        <th scope="col" class="px-6 py-3">Action</th>
      </tr>
    </thead>
    <tbody>
      <tr class="bg-white border-b hover:bg-gray-50">
        <td class="px-6 py-4 font-medium text-gray-900">John Doe</td>
        <td class="px-6 py-4">john@example.com</td>
        <td class="px-6 py-4"><a href="#" class="text-blue-600 hover:underline">Edit</a></td>
      </tr>
    </tbody>
  </table>
</div>
```

### 9. **Tabs**

```vue
<template>
  <ul
    class="flex flex-wrap text-sm font-medium text-center text-gray-500 border-b border-gray-200"
  >
    <li class="mr-2">
      <a
        href="#"
        data-tabs-target="#tab1"
        class="inline-block p-4 rounded-t-lg hover:text-gray-600 hover:bg-gray-50 active"
      >
        Tab 1
      </a>
    </li>
    <li class="mr-2">
      <a
        href="#"
        data-tabs-target="#tab2"
        class="inline-block p-4 rounded-t-lg hover:text-gray-600 hover:bg-gray-50"
      >
        Tab 2
      </a>
    </li>
  </ul>

  <div id="tab1" class="hidden p-4 bg-gray-50 rounded-lg">Tab 1 content</div>
  <div id="tab2" class="hidden p-4 bg-gray-50 rounded-lg">Tab 2 content</div>
</template>
```

---

## 🎨 Tùy Chỉnh Theme

Flowbite hỗ trợ nhiều theme:

- **Default** (current) - Modern & Clean
- **Minimal** - Simplistic design
- **Enterprise** - Professional look
- **Playful** - Fun & Colorful
- **Mono** - Black & White

Để đổi theme, sửa trong `style.css`:

```css
/* Chọn một trong những dòng này: */
@import "flowbite/src/themes/default";
/* @import "flowbite/src/themes/minimal"; */
/* @import "flowbite/src/themes/enterprise"; */
/* @import "flowbite/src/themes/playful"; */
/* @import "flowbite/src/themes/mono"; */
```

---

## 🔗 Tài Liệu Chính Thức

- Flowbite Vue: https://flowbite.com/docs/getting-started/vue/
- Component Docs: https://flowbite.com/docs/components/
- GitHub Starter: https://github.com/themesberg/tailwind-vue-starter

---

## ⚠️ Lưu Ý Quan Trọng

1. **`initFlowbite()` phải chạy sau khi app mount** - Đã được setup trong main.js
2. **Data attributes tự động hoạt động** - Không cần code JavaScript thêm
3. **Nếu dùng JavaScript API** - Import Modal, Dropdown, v.v từ 'flowbite'
4. **Responsive classes** - Sử dụng Tailwind breakpoints: `sm:`, `md:`, `lg:`, `xl:`
5. **Dark mode** - Thêm `dark:` prefix cho class (nếu cần)
