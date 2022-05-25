<template>
    <v-form>
    <v-text-field
      v-model="license"
      :prepend-inner-icon="icons.mdiAccountCheck"
      label="License"
      outlined
      dense
      placeholder="License"
    ></v-text-field>

    <v-btn color="primary" v-on:click="verify(license)">
      Vérifier la license
    </v-btn>
    <v-btn color="primary" style="display: none;" v-on:click="clicked()">
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
import { mdiAccountCheck } from '@mdi/js'
import { ref } from '@vue/composition-api'
import axios from 'axios';

export default {
    
    data() {
        return {
          license: '',
        }
    },

    methods: {
        verify(license) {
          axios.get('/check/licence', {
            params: {
              license: license
            }
          }).then((res) => {
            console.log(res.data);
            if (res.data) {

            }
          }).catch((error) => {
            console.error(error);
          });
        },
        clicked() {
            if (this.license) {
                document.getElementById('1').style.display = "none";
                document.getElementById('2').style.display = "block";
            }
        },
    },

    setup() {

        const license = ref('');

        return {
            license,
            // icons
            icons: {
                mdiAccountCheck,
            },
        }
    }
}
</script>