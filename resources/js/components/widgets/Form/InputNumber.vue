<template>
  <el-input-number
      :style="attrs.style"
      :class="attrs.className"
      :value="Number(value)"
      :min="attrs.min"
      :max="attrs.max"
      :step="attrs.step"
      :step-strictly="attrs.stepStrictly"
      :precision="attrs.precision"
      :size="attrs.size"
      :disabled="attrs.disabled"
      :controls="attrs.controls"
      :controls-position="attrs.controlsPosition"
      :label="attrs.label"
      :name="attrs.name"
      :placeholder="attrs.placeholder"
      @change="onChange"
  />
</template>
<script>
import {FormItemComponent} from "@/mixins.js";

export default {
  mixins: [FormItemComponent],
  updated() {
    if (!isNaN(this.value)) {
      this.$emit("change", Number(this.value));
    }
  }
};
</script>