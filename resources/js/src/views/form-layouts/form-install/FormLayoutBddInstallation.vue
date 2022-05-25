<template>
  <v-form>
    <v-text-field
      v-model="host"
      :prepend-inner-icon="icons.mdiAccountOutline"
      label="Host"
      outlined
      dense
      placeholder="Host"
    ></v-text-field>

    <v-text-field
      v-model="namedb"
      :prepend-inner-icon="icons.mdiDatabase"
      label="Nom de la base de donnée"
      outlined
      dense
      placeholder="Nom de la base de donnée"
    ></v-text-field>

    <v-text-field
      v-model="username"
      :prepend-inner-icon="icons.mdiDatabaseEditOutline"
      label="Nom d'utilisateur"
      outlined
      dense
      placeholder="Nom d'utilisateur"
    ></v-text-field>

    <v-text-field
      v-model="password"
      :prepend-inner-icon="icons.mdiDatabaseLock"
      label="Password"
      outlined
      dense
      type="password"
      placeholder="password"
    ></v-text-field>

    <v-btn color="primary" v-on:click="clicked()">
      Passez à l'étape 3
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
// eslint-disable-next-line object-curly-newline
import { mdiAccountOutline, mdiDatabase, mdiDatabaseEditOutline, mdiDatabaseLock } from '@mdi/js'
import { ref } from '@vue/composition-api'

export default {

  data: {
    host: '',
    namedb: '',
    username: '',
    password: '',
  },

  methods: {
    clicked() {
      if (this.host && this.namedb && this.username && this.password ) {
        document.getElementById('2').style.display = "none";
        document.getElementById('3').style.display = "block";
      }
    },
  },

  setup() {
    const host = ref('')
    const namedb = ref('')
    const username = ref('')
    const password = ref('')

    return {
      host,
      namedb,
      username,
      password,
      // icons
      icons: {
        mdiAccountOutline,
        mdiDatabase,
        mdiDatabaseEditOutline,
        mdiDatabaseLock,
      },
    }
  },
}
</script>
