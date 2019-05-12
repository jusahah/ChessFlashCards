<template>
  <div>
    <b-form @submit.prevent="onSubmit" @reset="onReset">
      <b-form-group id="input-group-4">
        <!-- Styled -->
        <b-form-file
          accept=".pgn"
          v-model="file"
          :state="Boolean(file)"
          placeholder="Choose a file..."
          drop-placeholder="Drop file here..."
        ></b-form-file>
        <!--
        <div class="mt-3">Selected file: {{ file ? file.name : '' }}</div>
        -->
      </b-form-group>
      <b-button type="submit" variant="primary" :disabled="!file">Submit</b-button>
    </b-form>  
  </div>
</template>

<script>

  import API from '@/api'
  
  export default {
    bame: 'UploadGames',
    data() {
      return {
        uploading: false,
        file: null,
      }
    },
    methods: {
      onSubmit() {
        console.log("Submit");
        console.log(this.file);

        let formData = new FormData();
        formData.append('pgns', this.file);

        this.uploading = true;

        return API.upload.uploadGames(formData)
        .then(() => {
          this.uploading = false;
        })

      },
      onReset() {
        this.file = null;
      }
    }
  }
</script>