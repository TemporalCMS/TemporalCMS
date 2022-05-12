<template>
    <v-form>
    <v-text-field
      v-model="license"
      :prepend-inner-icon="icons.mdiAccountOutline"
      label="Host"
      outlined
      dense
      placeholder="Host"
    ></v-text-field>

    <v-btn color="primary" v-on:click="clicked()">
      Passez à l'étape 2
    </v-btn>
    <v-btn
      type="reset"
      outlined
      class="mx-2"
    >
      Reset
    </v-btn>
  </v-form>
</template>
<script>
import { mdiAccountOutline, mdiEmailOutline, mdiCellphone, mdiLockOutline } from '@mdi/js'
import { ref } from '@vue/composition-api'

export default {
    
    data: {
        license: ref('')
    },

    methods: {
        clicked() {
            if (this.license) {
                document.getElementById('1').style.display = "none";
                document.getElementById('2').style.display = "block";
            }
        },
    },

    setup() {

        const license = this.data.license;

        return {
            license,
            // icons
            icons: {
                mdiAccountOutline,
            },
        }
    }
}
</script>