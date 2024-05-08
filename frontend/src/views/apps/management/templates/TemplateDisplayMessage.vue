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
          v-html="templateData.header"
        >
        </p>
        <span
          v-if="existMediaTemplate"
          class="text-justify p-1"
        >
          <img :src="require('@/assets/images/backgrounds/whatsapp/media.svg')" width="276px">
        </span>
        <p
          class="text-justify p-1"
          v-html="templateData.body"
        >
          {{templateData.body}} teste
        </p>
        <p
          class="text-justify px-1"
          v-html="templateData.footer"
          style="color: #8696a0; font-size: 10px"
        >
        </p>
      </div>
      <span
        v-for="(parameter, index) in templateData.parameters"
        :key="index"
      >
        <!-- Se o parâmetro for um BOTÃO -->
        <div
          class="rounded box-button ml-2"
          v-if="parameter.type_parameter_id == 2"
        >
          <div
            class=" text-center"
            style="padding-top: 7px"
          >
            <!-- Se for um botão de RESPOSTA RÁPIDA -->
            <span
              v-if="parameter.tem_url == null && parameter.tem_phone_number == null"
            >
              {{ parameter.tem_content }}
            </span>
            <!-- Se for um botão de chamada para ação -->
            <span
              v-else
            >
              <!-- Se a ação for uma url -->
              <span
                v-if="parameter.tem_url"
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
              {{ parameter.tem_content }}
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
    templateData: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      existMediaTemplate: false,
    }
  },
  methods: {
    //Verifica se existe alguma mídia associada ao template
    checkExistMediaTemplate() {
      var existMedia = null
      //Verifica se existe alguma mídia no template
      var existMedia = this.templateData.parameters.find(c => c.tem_media_name != null)
      //Caso exista alguma mídia no template
      if(existMedia) {
        this.existMediaTemplate = true
      }
    },
  },
  created() {
    this.checkExistMediaTemplate()
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
