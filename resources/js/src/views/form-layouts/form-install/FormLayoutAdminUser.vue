<template>
  <v-form>
    <v-text-field
      v-model="pseudo"
      :prepend-inner-icon="icons.mdiAccountOutline"
      label="Pseudo"
      outlined
      dense
      placeholder="Pseudo"
    ></v-text-field>

    <v-text-field
      v-model="email"
      :prepend-inner-icon="icons.mdiEmailOutline"
      label="Adresse mail"
      outlined
      dense
      type="email"
      placeholder="Adresse mail"
    ></v-text-field>

    <v-text-field
      v-model="password"
      :prepend-inner-icon="icons.mdiLockOutline"
      label="Mot de passe"
      outlined
      dense
      type="password"
      placeholder="Mot de passe"
    ></v-text-field>


    <v-btn color="primary">
      Passez à l'étape 4
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
import { mdiAccountOutline, mdiEmailOutline, mdiLockOutline } from '@mdi/js'
import { ref } from '@vue/composition-api'

export default {
  setup() {
    const pseudo = ref('')
    const email = ref('')
    const password = ref('')

    return {
      pseudo,
      email,
      password,
      // icons
      icons: {
        mdiAccountOutline,
        mdiEmailOutline,
        mdiLockOutline,
      },
    }
  },
}
</script>
