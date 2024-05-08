<template>
  <div class="d-flex justify-content-center">
    <div
      class="rounded display-preview position-relative py-1"
    >
      <!-- Box com a mensagem digitada -->
      <div
        class="rounded box-message mt-2 ml-2"
      >
        <p
          class="text-justify font-weight-bold px-1 pt-1"
          v-html="quickMessageData.header"
        >
        </p>
        <span
          v-if="existMediaQuickMessage && (quickMessageData.type_format_message_id == null || quickMessageData.type_format_message_id == 1) "
          class="text-justify p-1"
        >
          <img :src="require('@/assets/images/backgrounds/whatsapp/media.svg')" width="276px">
        </span>
        <!-- Se o formato da mensagem for -->
        <p
          class="text-justify p-1"
          v-html="quickMessageData.content"
          v-if="quickMessageData.type_format_message_id == null || quickMessageData.type_format_message_id == 1"
        >
        </p>
        <!-- Se o formato da mensagem for áudio -->
        <p
          v-if="quickMessageData.type_format_message_id == 2"
        >
          <audio
            controls 
            style="width: 90%; margin-left: 15px;"
          >
            <source :src="baseUrlStorage+`public/quickMessages/quickMessage${quickMessageData.parameters[0].quick_message_id}/header/${quickMessageData.parameters[0].qui_media_name}`" type="audio/mpeg">
            Your browser does not support the audio tag.
          </audio>
        </p>
        <!-- Se o formato da mensagem for um arquivo -->
        <p
          v-if="quickMessageData.type_format_message_id == 3"
        >
          <feather-icon
            icon="FileIcon"
            size="16"
            style="vertical-align: text-top"
            class="ml-2"
            stroke="#5e50ee"
          />
          <a 
            target="_blank" 
            :href="baseUrlStorage+`public/quickMessages/quickMessage${quickMessageData.parameters[0].quick_message_id}/header/${quickMessageData.parameters[0].qui_media_name}`"
          > 
            <span>{{ quickMessageData.parameters[0].qui_media_original_name }}</span>
          </a>
        </p>
        <!-- Se o formato da mensagem for um vídeo -->
        <p
          v-if="quickMessageData.type_format_message_id == 4"
          class="text-center"
        >
          <video width="250" height="200" controls class="mb-2">
            <source :src="baseUrlStorage+`public/quickMessages/quickMessage${quickMessageData.parameters[0].quick_message_id}/header/${quickMessageData.parameters[0].qui_media_name}`" type="video/mp4">
          </video>
        </p>
        <p
          class="text-justify px-1"
          v-html="quickMessageData.footer"
          style="color: #8696a0; font-size: 10px"
        >
        </p>
      </div>

      <div
        class=" text-center my-1"
        style="padding-top: 7px"
        v-if="quickMessageData.qui_list_name"
      >
        <span
          class="rounded box-button px-2"
          style="padding: 2px"
        >
          <feather-icon
            icon="ListIcon"
            size="16"
            style="vertical-align: text-top"
          />
          {{ quickMessageData.qui_list_name }}
        </span>
      </div>
      <span
        v-for="(parameter, index) in quickMessageData.parameters"
        :key="index"
      >
        <!-- Se o parâmetro for um BOTÃO -->
        <div
          class="rounded box-button ml-2"
          v-if="parameter.type_parameter_id == 1"
        >
          <div
            class=" text-center"
            style="padding-top: 7px"
          >
            <!-- Se for um botão de RESPOSTA RÁPIDA -->
            <span
              v-if="parameter.qui_url == null && parameter.qui_phone_number == null"
            >
              {{ parameter.qui_content }}
            </span>
            <!-- Se for um botão de chamada para ação -->
            <span
              v-else
            >
              <!-- Se a ação for uma url -->
              <span
                v-if="parameter.qui_url"
              >
                <feather-icon
                  icon="ExternalLinkIcon"
                  size="16"
                  style="vertical-align: text-top"
                />
              </span>
              <!-- Se a ação for um número de telefone -->
              <span
                v-else
              >
                <feather-icon
                  icon="PhoneIcon"
                  size="16"
                  style="vertical-align: text-top"
                />
              </span>
              {{ parameter.qui_content }}
            </span>
          </div>
        </div>
      </span>
    </div>
  </div>

</template>

<script>
import {
  BCard,
} from 'bootstrap-vue'


export default {
  components: {
    BCard,
  },
  props: {
    quickMessageData: {
      type: Object,
      required: true,
    },
    baseUrlStorage: {
      type: String,
      required: false,
    },
  },
  data() {
    return {
      existMediaQuickMessage: false,
    }
  },
  methods: {
    //Verifica se existe alguma mídia associada ao template
    checkExistMediaQuickMessage() {
      var existMedia = null
      //Verifica se existe alguma mídia no template
      var existMedia = this.quickMessageData.parameters.find(c => c.qui_media_name != null)
      //Caso exista alguma mídia no template
      if(existMedia) {
        this.existMediaQuickMessage = true
      }
    },
  },
  created() {
    this.checkExistMediaQuickMessage()
  },
  setup() {
    return {
      
    }
  }
}
</script>

<style lang="scss" scoped>
.per-page-selector {
  width: 90px;
}
.display-preview {
  width: 350px; 
  min-height: 400px; 
  margin-top:10px; 
  background: url('../../../../assets/images/backgrounds/whatsapp/whatsapp-background.png') left top repeat;
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
}

.box-message {
  background-color: white;
  width: 305px;
  min-height: 150px;
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
}

.box-button {
  margin-top: 5px;
  background-color: white;
  width: 305px;
  height: 40px;
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
  color: #009de2;
  font-size: 16px;
}
</style>

<style lang="scss">

</style>
