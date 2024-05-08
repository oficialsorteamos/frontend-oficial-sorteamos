<template id="t">
  <div>
    <b-row>
      <b-col
        md="8"
        class="border-right"
      >
        <!-- ScrollArea: Chat & Contacts -->
        <vue-perfect-scrollbar
          :settings="perfectScrollbarSettings"
          class="scroll-area"
        >
          <div
            class="pr-2" 
            style="height: 750px"
          >
              <!-- A função handleSubmit só deixa a o formulário ser submetido (só chama a função onSubmit) caso todos os campos do form satisfação os os pré-requisitos -->
            <validation-observer
              #default="{ handleSubmit }"
              ref="refFormObserver"
            >
              <b-form
                enctype="multipart/form-data"
                @submit.prevent="handleSubmit(onSubmit)"
              >
                <input
                  type="hidden"
                  id="variablesTags"
                  v-bind:value="templateMessageLocal.variablesTags = templateVariables"
                />
                <b-row>
                  <b-col
                    md="12"
                  >
                    <!-- Nome do template -->
                    <validation-provider
                      #default="validationContext"
                      name="Name"
                      rules="required|alpha-dash|min:4"
                    >
                      <b-form-group
                        :label="$t('chat.chatModalNewTemplateMessageHandler.name')"
                        label-for="task-title"
                      >
                        <b-form-input
                          id="task-title"
                          v-model="templateMessageLocal.tem_name"
                          autofocus
                          :state="getValidationState(validationContext)"
                          trim
                          :placeholder="$t('chat.chatModalNewTemplateMessageHandler.namePlaceholder')"
                          type="text"
                          :maxlength="512"
                          autocomplete="off"
                          @keyup="validationNameTemplate(); checkTemplateNameExist(templateMessageLocal.tem_name)"
                        />

                        <b-form-invalid-feedback>
                          {{ validationContext.errors[0] }}
                        </b-form-invalid-feedback>
                        <span 
                          v-if="errorTemplateName"
                          style="font-size: 0.857rem; color: #ea5455;"
                        >
                          {{ $t('chat.chatModalNewTemplateMessageHandler.nameExist') }}
                        </span>
                      </b-form-group>
                    </validation-provider> 
                </b-col>
              </b-row>
              <b-row>
                <b-col
                  md="12"
                  class="mb-1"
                >
                  <!-- Language -->
                  <b-form-group
                    :label="$t('chat.chatModalNewTemplateMessageHandler.category')"
                    label-for="vue-select"
                  >
                    <v-select
                      id="vue-select"
                      v-model="templateMessageLocal.category"
                      :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                      :options="categories"
                      :getOptionLabel="categories => categories.tem_name"
                      transition=""
                    >
                      <template #option="{ tem_name, tem_description }">
                        <b>{{ tem_name }}</b>
                        <br />
                        <cite>{{ tem_description }}</cite>
                      </template>
                      <template #search="{attributes, events}">
                        <input
                          class="vs__search"
                          :required="!templateMessageLocal.category"
                          v-bind="attributes"
                          v-on="events"
                        />
                      </template>
                    </v-select>
                  </b-form-group>
                </b-col>
              </b-row>
              <b-row>
                <b-col
                    md="6"
                    class="mb-1"
                >
                  <!-- Language -->
                  <b-form-group
                    :label="$t('chat.chatModalNewTemplateMessageHandler.language')"
                    label-for="vue-select"
                  >
                    <v-select
                      id="vue-select"
                      v-model="templateMessageLocal.language"
                      :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                      :options="languages"
                      :getOptionLabel="languages => languages.tem_name"
                      transition=""
                    >
                      <template #search="{attributes, events}">
                        <input
                          class="vs__search"
                          :required="!templateMessageLocal.language"
                          v-bind="attributes"
                          v-on="events"
                        />
                      </template>
                    </v-select>
                  </b-form-group>
                </b-col>
                <b-col
                    md="6"
                    class="mb-1"
                >
                  <!-- Language -->
                  <b-form-group
                    :label="$t('chat.chatModalNewTemplateMessageHandler.channel')"
                    label-for="vue-select"
                  >
                    <v-select
                      id="vue-select"
                      v-model="templateMessageLocal.channel"
                      :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                      :options="channels"
                      :getOptionLabel="channels => channels.cha_name"
                      transition=""
                    >
                      <template #search="{attributes, events}">
                        <input
                          class="vs__search"
                          :required="!templateMessageLocal.channel"
                          v-bind="attributes"
                          v-on="events"
                        />
                      </template>
                    </v-select>
                  </b-form-group>
                </b-col>
              </b-row>

              <!-- Cabeçalho -->
              <b-badge
                variant="success"
                class="badge-glow mt-1 mb-2 p-1"
              >
                <span
                  style="font-size: 14px"
                >
                  {{ $t('chat.chatModalNewTemplateMessageHandler.header') }}
                </span>
              </b-badge>
              <div
                class="w-100 border-success rounded mb-2 p-1"
              >
                <b-row>
                  <b-col
                    md="6"
                    class="mb-1"
                  >
                    <!-- Tipo de botões -->
                    <b-form-group
                      :label="$t('chat.chatModalNewTemplateMessageHandler.typeHeader')"
                      label-for="vue-select"
                    >
                      <v-select
                        id="vue-select"
                        v-model="templateMessageLocal.typeHeader"
                        :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                        :options="typeHeaders"
                        :getOptionLabel="typeHeaders => typeHeaders.tem_name"
                        transition=""
                        @input="clearHeaderData"
                      />
                    </b-form-group> 
                  </b-col>  
                </b-row>
                <b-row>
                  <b-col
                    md="8"
                    class="mb-1"
                  >
                    <span
                      v-if="templateMessageLocal.typeHeader"
                    >
                      <!-- Se o tipo de cabeçalho for um TEXTO -->
                      <span
                        v-if="templateMessageLocal.typeHeader.id == 1"
                      >
                        <!-- Texto do cabeçalho -->
                        <validation-provider
                          #default="validationContext"
                          :name="$t('chat.chatModalNewTemplateMessageHandler.text')"
                          rules="required|min:1"
                        >
                          <b-form-group
                            :label="$t('chat.chatModalNewTemplateMessageHandler.text')"
                            label-for="task-title"
                          >
                            <b-form-input
                              id="task-title"
                              v-model="templateMessageLocal.header"
                              :state="getValidationState(validationContext)"
                              trim
                              :placeholder="$t('chat.chatModalNewTemplateMessageHandler.textPlaceholder')"
                              type="text"
                              :maxlength="60"
                              autocomplete="off"
                            />
                            <b-form-invalid-feedback>
                              {{ validationContext.errors[0] }}
                            </b-form-invalid-feedback>
                          </b-form-group>
                        </validation-provider>
                      </span>
                      <!-- Se for alguma MÍDIA no cabeçalho -->
                      <span
                        v-else
                      >
                        <!-- Texto do cabeçalho -->
                        <validation-provider
                          #default="validationContext"
                          :name="$t('chat.chatModalNewTemplateMessageHandler.media')"
                          rules="required"
                        >
                          <b-form-group
                            label="Media"
                            label-for="task-title"
                          >
                            <b-form-file
                              v-model="fileSelected"
                              ref="importFileTemplate"
                              name="importFileTemplate"
                              id="importFileTemplate"
                              :state="getValidationState(validationContext)"
                              accept=".jpeg, .jpg, .png, .pdf, .doc, .docx, .xls, .xlsx, .mp4"
                              :placeholder="$t('chat.chatModalNewTemplateMessageHandler.filePlaceholder')"
                              :drop-placeholder="$t('chat.chatModalNewTemplateMessageHandler.filePlaceholder')"
                              @change="handleFileUpload"
                            />
                            <b-form-invalid-feedback>
                              {{ validationContext.errors[0] }}
                            </b-form-invalid-feedback>
                          </b-form-group>
                        </validation-provider>
                      </span>
                    </span>
                  </b-col>
                </b-row>
              </div>

              <!-- Corpo da Mensagem -->
              <b-badge
                variant="primary"
                class="badge-glow mt-1 mb-1 p-1"
              >
                <span
                  style="font-size: 14px"
                >
                  {{ $t('chat.chatModalNewTemplateMessageHandler.body') }}
                </span>
              </b-badge>
              <div
                class="w-100 border-primary rounded p-1"
              >
                <b-row>
                    <b-col
                      md="8"
                      class="mb-1"
                    >
                      <!-- Content -->
                      <b-form-group
                        :label="$t('chat.chatModalNewTemplateMessageHandler.message')"
                        label-for="new-quick-message-content"
                      >
                        <quill-editor
                          id="quil-content"
                          v-model="templateMessageLocal.body"
                          :options="editorOption"
                          class="border-bottom-0"
                          ref="myEditor"
                          style="min-height:200px;"
                          @change="countVariablesText(); formatTextBody()"
                        />
                        <div
                          id="quill-toolbar"
                          class="d-flex justify-content-end border-top-0"
                        >
                          <twemoji-picker
                            :emojiData="emojiDataAll"
                            :emojiGroups="emojiGroups"
                            :skinsSelection="false"
                            :searchEmojisFeat="true"
                            :recentEmojisFeat="true"
                            :randomEmojiArray="['😀']"
                            searchEmojiPlaceholder="Search here."
                            searchEmojiNotFound="Emojis not found."
                            isLoadingLabel="Loading..."
                            @emojiUnicodeAdded="emojiSelected"
                            twemojiPath="https://cdnjs.cloudflare.com/ajax/libs/twemoji/14.0.2/"
                          >
                          </twemoji-picker>
                          <!-- Add a bold button -->
                          <button class="ql-bold" />
                          <!--
                          <button class="ql-italic" />
                          <button class="ql-underline" />
                          <button class="ql-align" />
                          <button class="ql-link" /> 
                          -->
                        </div>
                        <span
                          style="font-size: 0.857rem;"
                        >
                          <i>{{ totalCharactersBody }}/1024</i>
                        </span>
                      </b-form-group>
                    </b-col>
                    <b-col
                        md="4"
                        class="mb-1"
                      >
                      <!-- Tags -->
                      <div class="justify-content-between">
                        <h6 class="section-label mb-1">
                          {{ $t('chat.chatModalNewTemplateMessageHandler.variables') }}
                        </h6>
                      </div>
                      <span
                        v-for="(variable, index) in templateVariables"
                        :key="index"
                      >
                        <!-- Tag -->
                        <b-form-group
                          :label="variable"
                          label-for="vue-select"
                        >
                          <v-select
                            id="vue-select"
                            v-model="templateMessageLocal.parameters[index]"
                            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                            :options="typeVariables"
                            :getOptionLabel="typeVariables => typeVariables.tem_name"
                            transition=""
                          >
                            <template #search="{attributes, events}">
                              <input
                                class="vs__search"
                                :required="!templateMessageLocal.parameters[index]"
                                v-bind="attributes"
                                v-on="events"
                              />
                            </template>
                          </v-select>
                        </b-form-group>
                      </span>
                    </b-col>  
                  </b-row>
                </div>

                <!-- Rodapé -->
                <b-badge
                  variant="secondary"
                  class="badge-glow mt-3 mb-1 p-1"
                >
                  <span
                    style="font-size: 14px"
                  >
                    {{ $t('chat.chatModalNewTemplateMessageHandler.footer') }}
                  </span>
                </b-badge>
              <div
                class="w-100 border-secondary rounded p-1"
              >
                <b-row>
                    <b-col
                      md="8"
                      class="mb-1"
                    >
                      <!-- Title -->
                      <validation-provider
                        #default="validationContext"
                        :name="$t('chat.chatModalNewTemplateMessageHandler.message')"
                        rules=""
                      >
                        <b-form-group
                          :label="$t('chat.chatModalNewTemplateMessageHandler.message')"
                          label-for="task-title"
                        >
                          <b-form-input
                            id="task-title"
                            v-model="templateMessageLocal.footer"
                            :state="getValidationState(validationContext)"
                            trim
                            :placeholder="$t('chat.chatModalNewTemplateMessageHandler.messagePlaceholder')"
                            type="text"
                            :maxlength="60"
                            autocomplete="off"
                          />
                          <b-form-invalid-feedback>
                            {{ validationContext.errors[0] }}
                          </b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider> 
                    </b-col>  
                  </b-row>
                </div>

                <!-- Botões -->
                <b-badge
                  variant="dark"
                  class="badge-glow mt-4 mb-1 p-1"
                >
                  <span
                    style="font-size: 14px"
                  >
                    {{ $t('chat.chatModalNewTemplateMessageHandler.buttons') }}
                  </span>
                </b-badge>
                <div
                  class="w-100 border-dark rounded p-1"
                >
                  <b-row>
                    <b-col
                      md="6"
                      class="mb-1"
                    >
                      <!-- Tipo de botões -->
                      <b-form-group
                        :label="$t('chat.chatModalNewTemplateMessageHandler.typeButton')"
                        label-for="vue-select"
                      >
                        <v-select
                          id="vue-select"
                          v-model="templateMessageLocal.typeButton"
                          :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                          :options="typeButtons"
                          :getOptionLabel="typeButtons => typeButtons.tem_name"
                          transition=""
                          :disabled="templateButtons.length > 0? true : false"
                          @input="checkLimitButtons"
                        />
                      </b-form-group>
                    </b-col>
                    <b-col
                      md="3"
                      class="mb-1"
                    >
                      <b-button
                        v-ripple.400="'rgba(255, 255, 255, 0.15)'"
                        variant="primary"
                        @click="repeateAgain"
                        class="mt-2"
                        :disabled="addButtonDisabled"
                      >
                        <feather-icon
                          icon="PlusIcon"
                          class="mr-25"
                        />
                        <span>{{ $t('chat.chatModalNewTemplateMessageHandler.add') }}</span>
                      </b-button>
                    </b-col>
                  </b-row>
                  <b-form
                    ref="form"
                    :style="{height: trHeight}"
                    class="repeater-form"
                    @submit.prevent="repeateAgain"
                  >
                    <!-- Se for um botão do tipo RESPOSTA RÁPIDA -->
                    <span
                      v-if="templateMessageLocal.typeButton? templateMessageLocal.typeButton.id == 1 : ''"
                    >
                      <!-- Row Loop -->
                      <div
                        v-for="(item, index) in templateButtons"
                        :id="index"
                        :key="index"
                        ref="row"
                      >
                        <h5
                          style="font-size: 12px"
                        >
                          {{ $t('chat.chatModalNewTemplateMessageHandler.button') }} {{ index+1 }}
                        </h5>
                        <p
                          class="border-secondary rounded p-1"
                          style="width: 100%"
                        >
                          <b-button
                            v-ripple.400="'rgba(234, 84, 85, 0.15)'"
                            variant="danger"
                            class="btn-icon float-right"
                            @click="removeItem(index)"
                          >
                            <feather-icon
                              icon="TrashIcon"
                              size="16"
                            />
                          </b-button>
                          <b-row>
                            <!-- Item Name -->
                            <b-col md="10">
                              <validation-provider
                                #default="validationContext"
                                :name="$t('chat.chatModalNewTemplateMessageHandler.label')"
                                rules="required|min:1"
                              >
                                <b-form-group
                                  :label="$t('chat.chatModalNewTemplateMessageHandler.label')"
                                  label-for="item-name"
                                >
                                  <b-form-input
                                    v-model="templateMessageLocal.buttonLabel[index]"
                                    id="item-name"
                                    type="text"
                                    :state="getValidationState(validationContext)"
                                    trim
                                    :maxlength="20"
                                    autocomplete="off"
                                    :placeholder="$t('chat.chatModalNewTemplateMessageHandler.buttonPlaceholder')"
                                  />
                                  <b-form-invalid-feedback>
                                    {{ validationContext.errors[0] }}
                                  </b-form-invalid-feedback>
                                </b-form-group>
                              </validation-provider>
                            </b-col>
                          </b-row>
                        </p>
                        <div>&nbsp;</div>
                      </div>
                    </span>

                    <!-- Se for um botão de CHAMADA PARA AÇÃO -->
                    <span
                      v-else
                    >
                      <!-- Row Loop -->
                      <div
                        v-for="(item, index) in templateButtons"
                        :id="index"
                        :key="index"
                        ref="row"
                      >
                        <h5
                          style="font-size: 12px"
                        >
                          {{ $t('chat.chatModalNewTemplateMessageHandler.button') }} {{ index+1 }}
                        </h5>
                        <p
                          class="border-secondary rounded p-1"
                          style="width: 100%"
                        >
                          <b-button
                            v-ripple.400="'rgba(234, 84, 85, 0.15)'"
                            variant="danger"
                            class="btn-icon float-right"
                            @click="removeItem(index)"
                          >
                            <feather-icon
                              icon="TrashIcon"
                              size="16"
                            />
                          </b-button>
                          <b-row>
                            <!-- Item Name -->
                            <b-col md="10" style="padding-left: 0px !important">
                                <!-- Tipo de botões -->
                              <b-form-group
                                :label="$t('chat.chatModalNewTemplateMessageHandler.callAction')"
                                label-for="vue-select"
                              >
                                <v-select
                                  id="vue-select"
                                  v-model="templateMessageLocal.callActions[index]"
                                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                                  :options="typeCallActions"
                                  :getOptionLabel="typeCallActions => typeCallActions.tem_name"
                                  transition=""
                                  :disabled="callActionDisabled"
                                  @input="clearValuesButton(index)"
                                >
                                  <template #search="{attributes, events}">
                                    <input
                                      class="vs__search"
                                      :required="!templateMessageLocal.callActions[index]"
                                      v-bind="attributes"
                                      v-on="events"
                                    />
                                  </template>
                                </v-select>
                              </b-form-group>
                            </b-col>
                          </b-row>
                          <!-- Se a CHAMADA PARA AÇÃO for uma URL -->
                          <span
                            style="width: 100px"
                            v-if="templateMessageLocal.callActions[index].id == 1"
                          >
                            <b-row>
                              <!-- Nome do Botão -->
                              <b-col
                                md="6"
                              >
                                <validation-provider
                                  #default="validationContext"
                                  :name="$t('chat.chatModalNewTemplateMessageHandler.label')"
                                  rules="required|min:1"
                                >
                                  <b-form-group
                                    label="Label"
                                    label-for="task-title"
                                  >
                                    <b-form-input
                                      id="task-title"
                                      v-model="templateMessageLocal.buttonLabel[index]"
                                      autofocus
                                      :state="getValidationState(validationContext)"
                                      trim
                                      :placeholder="$t('chat.chatModalNewTemplateMessageHandler.buttonPlaceholder')"
                                      type="text"
                                      :maxlength="20"
                                      autocomplete="off"
                                    />
                                    <b-form-invalid-feedback>
                                      {{ validationContext.errors[0] }}
                                    </b-form-invalid-feedback>
                                  </b-form-group>
                                </validation-provider> 
                              </b-col>

                              <!-- URL -->
                              <b-col
                                md="6"
                              >
                                <validation-provider
                                  #default="validationContext"
                                  :name="$t('chat.chatModalNewTemplateMessageHandler.url')"
                                  rules="required|url"
                                >
                                  <b-form-group
                                    :label="$t('chat.chatModalNewTemplateMessageHandler.url')"
                                    label-for="task-title"
                                  >
                                    <b-form-input
                                      id="task-title"
                                      v-model="templateMessageLocal.buttonUrl"
                                      :state="getValidationState(validationContext)"
                                      trim
                                      :placeholder="$t('chat.chatModalNewTemplateMessageHandler.urlPlaceholder')"
                                      type="text"
                                      autocomplete="off"
                                    />
                                    <b-form-invalid-feedback>
                                      {{ validationContext.errors[0] }}
                                    </b-form-invalid-feedback>
                                  </b-form-group>
                                </validation-provider> 
                              </b-col>
                            </b-row>
                          </span>
                          <!-- Se a CHAMADA PARA AÇÃO for um número de telefone -->
                          <span
                            style="width: 100px"
                            v-if="templateMessageLocal.callActions[index].id == 2"
                          >
                            <b-row>
                              <!-- Nome do Botão -->
                              <b-col
                                md="6"
                              >
                                <validation-provider
                                  #default="validationContext"
                                  :name="$t('chat.chatModalNewTemplateMessageHandler.label')"
                                  rules="required|min:1"
                                >
                                  <b-form-group
                                    label="Label"
                                    label-for="task-title"
                                  >
                                    <b-form-input
                                      id="task-title"
                                      v-model="templateMessageLocal.buttonLabel[index]"
                                      autofocus
                                      :state="getValidationState(validationContext)"
                                      trim
                                      :placeholder="$t('chat.chatModalNewTemplateMessageHandler.buttonPlaceholder')"
                                      type="text"
                                      :maxlength="20"
                                      autocomplete="off"
                                    />
                                    <b-form-invalid-feedback>
                                      {{ validationContext.errors[0] }}
                                    </b-form-invalid-feedback>
                                  </b-form-group>
                                </validation-provider> 
                              </b-col>

                              <!-- URL -->
                              <b-col
                                md="6"
                              >
                                <validation-provider
                                  #default="{ errors }"
                                  :name="$t('chat.chatModalNewTemplateMessageHandler.phoneNumber')"
                                  rules="required|min:12"
                                >
                                  <b-form-group
                                    label-for="user-phone"
                                    :label="$t('chat.chatModalNewTemplateMessageHandler.phoneNumber')+'*'"
                                  >
                                    <!-- Phone Number -->
                                    <VuePhoneNumberInput  
                                      v-model="templateMessageLocal.buttonPhone"
                                      :required="true"
                                      class="mb-1"
                                      @update="setPhoneNumber"
                                      default-country-code="BR"
                                    />
                                    <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                                      {{ errors[0] }}
                                    </b-form-invalid-feedback>
                                  </b-form-group>
                                </validation-provider>
                                <input
                                  type="hidden"
                                  id="phoneNumber"
                                  v-bind:value="templateMessageLocal.phoneNumber = phoneNumber"
                                />
                              </b-col>
                            </b-row>
                          </span>
                        </p>
                        <div>&nbsp;</div>
                      </div>
                    </span>
                  </b-form>
                </div>
                <!-- Form Actions -->
                <div class="d-flex mt-2 modal-footer">
                  <!-- Se o nome do template contém algum erro ou se não foi digitado o texto do body, não deixa salvar o template -->
                  <b-button
                    v-ripple.400="'rgba(255, 255, 255, 0.15)'"
                    variant="primary"
                    class="mr-2"
                    type="submit"
                    :disabled="errorTemplateName || totalCharactersBody == 0"
                  >
                    <feather-icon
                      icon="SaveIcon"
                      class="mr-50"
                    />
                    <span class="align-middle">{{ $t('chat.chatModalNewTemplateMessageHandler.save') }}</span>
                  </b-button>
                </div>
              </b-form>
            </validation-observer>
          </div>
        </vue-perfect-scrollbar>
      </b-col>
      <b-col
        md="4"
      >
        <vue-perfect-scrollbar
          :settings="perfectScrollbarSettings"
          class="scroll-area"
        >
          <div
            class="pr-1" 
            style="height: 750px"
          >
            <h6 class="section-label mb-1">
              {{ $t('chat.chatModalNewTemplateMessageHandler.preview') }}
            </h6>
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
                    v-html="templateMessageLocal.header"
                  >
                  </p>
                  <span
                    v-if="templateMessageLocal.typeHeader? templateMessageLocal.typeHeader.id != 1 : ''"
                    class="text-justify p-1"
                  >
                    <img :src="require('@/assets/images/backgrounds/whatsapp/media.svg')" width="276px">
                  </span>
                  <p
                    class="text-justify p-1"
                    v-html="bodyTextFormatted"
                  >
                  </p>
                  <p
                    class="text-justify px-1"
                    v-html="templateMessageLocal.footer"
                    style="color: #8696a0; font-size: 10px"
                  >
                  </p>
                </div>
                <span
                  v-for="(item, index) in templateButtons"
                  :key="index"
                >
                  <div
                    class="rounded box-button ml-2"
                  >
                    <div
                      class=" text-center"
                      style="padding-top: 7px"
                    >
                      <!-- Se for um botão de RESPOSTA RÁPIDA -->
                      <span
                        v-if="templateMessageLocal.typeButton? templateMessageLocal.typeButton.id == 1 : ''"
                      >
                        {{ templateMessageLocal.buttonLabel[index] }}
                      </span>
                      <!-- Se for um botão de chamada para ação -->
                      <span
                        v-else
                      >
                        <!-- Se a ação for uma url -->
                        <span
                          v-if="templateMessageLocal.callActions[index].id == 1"
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
                        {{ templateMessageLocal.buttonLabel[index] }}
                      </span>
                    </div>
                  </div>
                </span>
              </div>
            </div>
          </div>
        </vue-perfect-scrollbar>
      </b-col>
    </b-row>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BTable, BFormCheckbox, BCard, BRow, BCol, 
  BAvatar, BBadge, BFormInvalidFeedback, BListGroup, BListGroupItem, BFormFile,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import axios from '@axios'
import { toRefs} from '@vue/composition-api'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useChatModalNewTemplateMessageHandler from './useChatModalNewTemplateMessageHandler'
import { TwemojiPicker } from '@kevinfaguiar/vue-twemoji-picker'
import EmojiAllData from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-all-groups.json'
import EmojiDataAnimalsNature from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-group-animals-nature.json'
import EmojiDataFoodDrink from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-group-food-drink.json'
import EmojiGroups from '@kevinfaguiar/vue-twemoji-picker/emoji-data/emoji-groups.json'
import formValidation from '@core/comp-functions/forms/form-validation'
import VuePhoneNumberInput from 'vue-phone-number-input'
import { quillEditor } from 'vue-quill-editor'
import flatPickr from 'vue-flatpickr-component'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { heightTransition } from '@core/mixins/ui/transition'
import Vue from 'vue'
import VueClipboard from 'vue-clipboard2'


// Faz com que seja possível copiar dentro de modals
VueClipboard.config.autoSetContainer = true 

Vue.use(VueClipboard)

export default {
  components: {
    BButton,
    BForm,
    BModal,
    VBModal,
    BFormInput,
    BFormGroup,
    vSelect,
    BTable,
    BFormCheckbox,
    BCard,
    BRow,
    BCol,
    BBadge,
    BAvatar,
    BFormInvalidFeedback,
    BListGroup,
    BListGroupItem,
    BFormFile,
    
    // Form Validation
    ValidationProvider,
    ValidationObserver,
    
    //Editor
    quillEditor,
    flatPickr,

    //Phone
    VuePhoneNumberInput,

    //Emojis
    'twemoji-picker': TwemojiPicker,
    EmojiGroups,
    EmojiDataFoodDrink,
    EmojiDataAnimalsNature,

    //Toast Notification
    ToastificationContent,

    //Scroll
    VuePerfectScrollbar,
  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  mixins: [heightTransition],
  props: {
    templateMessage: {
      type: Object,
      required: true,
    },
    channel: {
      type: Object,
      required: false,
    },
    channelId: {
      type: Number,
      required: false,
    },
    clearNewQuickMessageData: {
      type: Function,
      required: true,
    },
    checkTemplateNameExist: {
      type: Function,
      required: true,
    },
    errorTemplateName: {
      type: Boolean,
      required: true,
    },
  },
  computed: {
    emojiDataAll() {
      return EmojiAllData;
    },
    emojiGroups() {
      return EmojiGroups;
    }
  },
  data() {
    return {
      selectMode: 'single',
      selected: [],
      templateButtons: [],
      templateVariables: null,
      typeVariables: [],
      typeButtons: [],
      typeCallActions: [],
      typeHeaders: [],
      callActionDisabled: false,
      categories: [],
      languages: [],
      channels: [],
      totalCharactersBody: 0,
      addButtonDisabled: true,
      phoneNumber: '',
      bodyTextFormatted: '',
      fileSelected: '',
    }
  },
  methods: {
    //Insere emojis
    emojiSelected: function(emoji) {
      const range = this.$refs.myEditor.quill.getSelection()
      this.$refs.myEditor.quill.insertText(range.index, emoji)
    },
    //Conta quantas e quais variáveis estão presentes no texto e limita a quantidade de caracteres no texto
    countVariablesText() {
      //Conta a quantidade variáveis ( {{}} ) no texto digitado
      this.templateVariables = this.templateMessageLocal.body.match(/{{\d+}}/g)
      
      //Define o limite total de caracteres em 1024
      let limit = 1024
      //Se o total de caracteres digitados for maior que o limite
      if (this.$refs.myEditor.quill.getLength() > limit) {
        //Deleta todos os caracteres excedentes ao limite
        this.$refs.myEditor.quill.deleteText(limit, this.$refs.myEditor.quill.getLength())
      }

      this.totalCharactersBody = this.$refs.myEditor.quill.getLength()-1

      /*
      var content = this.$refs.myEditor.quill.getContents()
      console.log(content.ops[0])
      
      console.log(this.$refs.myEditor.quill.getText(this.$refs.myEditor.quill.getLength()-2, this.$refs.myEditor.quill.getLength()-1))
      //this.$refs.myEditor.quill.deleteText(6, this.$refs.myEditor.quill.getLength())
      var lastCharacter = this.$refs.myEditor.quill.getText(this.$refs.myEditor.quill.getLength()-4, this.$refs.myEditor.quill.getLength()-1)
      
      var range = this.$refs.myEditor.quill.getSelection()

      var middleCharacter = this.$refs.myEditor.quill.getText(range.index-2, 3)
      
      var hasCharacterAfter = this.$refs.myEditor.quill.getText(range.index, 1)
      var hasBreaklineBefore = this.$refs.myEditor.quill.getText(range.index-3, 3)

      var hasCharacterBefore = this.$refs.myEditor.quill.getText(range.index-1, 1)
      var hasBreaklineAfter = this.$refs.myEditor.quill.getText(range.index-1, 3)

      console.log('hasCharacterBefore')
      console.log(hasCharacterBefore)
      
      var hasCharacterBefore = /\r|\n/.exec(hasCharacterBefore)
      var hasBreaklineAfter = /\r|\n/.exec(hasBreaklineAfter)

      var hasAlphanumericsAfter = /\r|\n/.exec(hasCharacterAfter)
      var hasBreakLineBefore = /\r|\n/.exec(hasBreaklineBefore)

      const matchLastCharacter = /\r|\n/.exec(lastCharacter)
      const matchMiddleCharacter = /\r|\n/.exec(middleCharacter)

      console.log('hasCharacterBefore')
      console.log(hasCharacterBefore)

      var totalParagraphs = (content.ops[0].insert.match(/\r|\n/g) || []).length
      console.log('totalParagraphs')
      console.log(totalParagraphs)
      console.log(matchMiddleCharacter)
 
      var startText = content.ops[0].insert.substr(0, 1)
      var hasParagraphStart = /\r|\n/.exec(startText)
      console.log('hasParagraphStart')
      console.log(hasParagraphStart)
 
      //Se já tiver duas quebras de linhas
      if(matchLastCharacter.input == '\n\n\n\n') {
        this.$refs.myEditor.quill.deleteText(range.index-2, 1, 'api')
      }

      console.log(range.index)
      console.log(this.$refs.myEditor.quill.getLength())
      //Se tiver quebra de linha e o usuário tentar adicionar outra quebra
      if(matchMiddleCharacter.input == '\n\n\n' && totalParagraphs > 3 && range.index < this.$refs.myEditor.quill.getLength()-1) {
        //Não deixa quebrar a linha
        this.$refs.myEditor.quill.deleteText(range.index-1, 1, 'api')
      }
      //Se o após o cursor tiver algum caracter que não seja uma quebra de linha e antes tenha quebra de linha
      if(!hasAlphanumericsAfter && hasBreakLineBefore.input == '\n\n\n') {
        //Não deixa quebrar a linha
        this.$refs.myEditor.quill.deleteText(range.index-1, 1, 'api')
      }
      //Se o após o cursor tiver algum caracter que não seja uma quebra de linha e antes tenha quebra de linha
      if(hasCharacterBefore.input == '\n' && hasBreaklineAfter.input == '\n\n\n') {
        //Não deixa quebrar a linha
        this.$refs.myEditor.quill.deleteText(range.index-1, 1, 'api')
      }
      if(hasParagraphStart) {
        this.$refs.myEditor.quill.deleteText(range.index-1, 1, 'api')
      }
    
      console.log(this.templateMessageLocal.body)
      //Se o texto possui duas quebras de linha, reduz a uma quebra de linha
      if(this.templateMessageLocal.body.includes('<p><br></p><p><br></p>')) {
        this.templateMessageLocal.body = this.templateMessageLocal.body.replace('<p><br></p><p><br></p>', '<p><br></p>')
        //this.$refs.myEditor.quill.setSelection(2, 0)
      }
      
      //Se for criado uma quebra de linha na primeira linha, remove a mesma
      var startText = this.templateMessageLocal.body.substr(0, 11)
      if(startText == "<p><br></p>") {
        this.templateMessageLocal.body = this.templateMessageLocal.body.replace('<p><br></p>', ' ')
      }
      */
    },
    //Altera o nome do template para o formato suportado pela API
    validationNameTemplate () {
      //Converte as caracteres em minúsculo
      var variableAux = this.templateMessageLocal.tem_name.toLowerCase()
      this.templateMessageLocal.tem_name = variableAux
      //Substitiu os espaços por underscore
      variableAux = this.templateMessageLocal.tem_name.split(' ').join('_')
      this.templateMessageLocal.tem_name = variableAux

      variableAux = this.templateMessageLocal.tem_name.replace(/[^a-zA-Z0-9_. ]/g, "")
      this.templateMessageLocal.tem_name = variableAux
    },
    formatTextBody() {
      var bodyTextFormattedAux = this.templateMessageLocal.body.replace("<br>", "")
      bodyTextFormattedAux = bodyTextFormattedAux.replace("<p>", "")
      this.bodyTextFormatted = bodyTextFormattedAux.replace("</p>", "<br>") 
    },
    handleFileUpload (event) {
      this.$emit('upload-file', event.target.files[0])
    },
    repeateAgain() {
      this.templateButtons.push({
        id: this.nextTodoId += this.nextTodoId,
      })

      this.$nextTick(() => {
        this.trAddHeight(this.$refs.row[0].offsetHeight)
      })

      this.checkLimitButtons()

      this.setCallAction()
    },
    checkLimitButtons() {
      if(this.templateMessageLocal.typeButton == '' || this.templateMessageLocal.typeButton == null) {
        this.addButtonDisabled = true
      }
      else {
        this.addButtonDisabled = false

        //Se o tipo de botão é mensagem rápida e já foram adicionados 3 botões
        if(this.templateMessageLocal.typeButton.id == 1 && this.templateButtons.length == 3) {
          //Não deixa adicionar mais botões
          this.addButtonDisabled = true
        }
        //Se for um botão do tipo chamada para ação e já foram adicionados 2 botões
        else if(this.templateMessageLocal.typeButton.id == 2 && this.templateButtons.length == 2) {
          //Não deixa adicionar mais botões
          this.addButtonDisabled = true
        }
        else {
          this.addButtonDisabled = false
        }
      }
    },
    setCallAction() {
      this.typeCallActionsAux = this.typeCallActions
      //Se o tipo de botão for CHAMADA PARA AÇÃO e o usuário tenha adicionado apenas um botão
      if(this.templateMessageLocal.typeButton.id == 2 && this.templateButtons.length == 1) {
        this.callActionDisabled = false
        if(this.templateMessageLocal.callActions[0] == null) {
          //Atribui por padrão o tipo URL de chamada para ação 
          this.templateMessageLocal.callActions[0] = this.typeCallActions[0]
        }
      } //Se o tipo de botão for CHAMADA PARA AÇÃO e o usuário tenha adicionado o segundo botão
      else if(this.templateMessageLocal.typeButton.id == 2 && this.templateButtons.length == 2) {
        //Desabilita o select
        this.callActionDisabled = true
        //Se o botão de chamada para ação for uma URL
        if(this.templateMessageLocal.callActions[0].id == 1) {
          //Atribui o tipo NÚMERO DE TELEFONE para o segundo botão
          this.templateMessageLocal.callActions[1] = this.typeCallActions[1]
        }
        else {
          //Atribui o tipo URL para o segundo botão
          this.templateMessageLocal.callActions[1] = this.typeCallActions[0]
        }
      }
    },
    clearValuesButton(index) {
      //Se a chamada para ação foi uma URL
      if(this.templateMessageLocal.callActions[index].id == 1) {
        this.templateMessageLocal.buttonUrl = ''
      }
      else {
        this.templateMessageLocal.buttonPhone = ''
      }
      this.templateMessageLocal.buttonLabel[index] = ''
    },
    clearHeaderData(typeHeader) {
      //Se o tipo de cabeçalho selecionado foi TEXTO
      if(typeHeader.id == 1) {
        //Limpa o campo de mídia
        document.getElementById("importFileTemplate").value = null;
      }
      else {
        //Limpa o campo de texto
        this.templateMessageLocal.header = ''
      }
    },
    removeItem(index) {
      this.templateButtons.splice(index, 1)
      this.trTrimHeight(this.$refs.row[0].offsetHeight)

      this.checkLimitButtons()
      this.setCallAction()
      this.clearValuesButton(index)

    },
    //Seta número completo
    setPhoneNumber: function(data) {
      this.phoneNumber = data.formattedNumber
    },
    initTrHeight() {
      this.trSetHeight(null)
      this.$nextTick(() => {
        this.trSetHeight(this.$refs.form.scrollHeight)
      })
    },
    onCopy: function (e) {
      this.$toast({
        component: ToastificationContent,
        props: {
          title: 'Copied Tag!',
          icon: 'CheckIcon',
          variant: 'success',
        },
      })
    },
    onError: function (e) {
      this.$toast({
        component: ToastificationContent,
        props: {
          title: 'Failed to Copy!',
          icon: 'AlertTriangleIcon',
          variant: 'success',
        },
      })
    }
  },
  mounted() {
    this.initTrHeight()

    //Pré-seleciona um canal
    if(this.channel) {
      this.templateMessageLocal.channel = this.channel
    }
  },
  destroyed() {
    window.removeEventListener('resize', this.initTrHeight)
  },
  created() { 
    //Traz todos os tipos de variáveis para um template
    axios
      .get('/api/chat/fetch-type-variables/A')
      .then(response => {
        //console.log(response.data)
        this.typeVariables = response.data.typeVariables
      });
    
    //Traz os tipos de botões
    axios
      .get('/api/chat/fetch-type-buttons/A')
      .then(response => {
        //console.log(response.data)
        this.typeButtons = response.data.typeButtons
      });

    //Traz os tipos de cabeçalho
    axios
      .get('/api/chat/fetch-type-headers/A')
      .then(response => {
        //console.log(response.data)
        this.typeHeaders = response.data.typeHeaders
      });
    
    //Traz os tipos de chamadas para ação
    axios
      .get('/api/chat/fetch-type-call-actions/A')
      .then(response => {
        //console.log(response.data)
        this.typeCallActions = response.data.typeCallActions
      });

    //Traz todos as categorias de um template
    axios
      .get('/api/chat/fetch-template-categories')
      .then(response => {
        //console.log(response.data)
        this.categories = response.data.categories
      });

    //Traz todos os idiomas de um template
    axios
      .get('/api/chat/fetch-template-languages/A')
      .then(response => {
        //console.log(response.data)
        this.languages = response.data.languages
      });
    
    //Traz todos os canais oficiais
    axios
      .get('/api/management/channel/fetch-channels-official-api/1')
      .then(response => {
        this.channels = response.data

        if(this.channelId) {
          var channelSelected = this.channels.find(c => c.id === this.channelId)
          this.templateMessageLocal.channel = channelSelected
        }
      });
    
    

      this.countVariablesText()
    
    window.addEventListener('resize', this.initTrHeight)
  },
  setup(props,{ emit }) {
    const perfectScrollbarSettings = {
      maxScrollbarLength: 150,
    }

    const {
      templateMessageLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useChatModalNewTemplateMessageHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearActionData)

    const bindings = {
      enter: {
        key: 13,
        shiftKey: null,
        handler: () => {
          console.log(quill)
        }
      }
    }

    const editorOption = {
      formats: [
        'bold',
      ],
      modules: {
        toolbar: '#quill-toolbar',
        keyboard: {
          bindings
        },
      },
      placeholder: 'Write your content',
      clipboard: {
        matchVisual: false // https://quilljs.com/docs/modules/clipboard/#matchvisual
      }
    }


    return {
      // Add New Event
      templateMessageLocal,
      resetTransferLocal,
      onSubmit,

      //Quill Editor
      editorOption,
      
      
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,

      perfectScrollbarSettings,
    }
  },
}
import 'vue-phone-number-input/dist/vue-phone-number-input.css';
</script>
<style lang="scss" scoped>
@import '~@core/scss/base/bootstrap-extended/include';

.assignee-selector {
  ::v-deep .vs__dropdown-toggle {
  padding-left: 0;
  }
}

#quil-content ::v-deep {
  > .ql-container {
    border-bottom: 0;
    min-height: inherit;
  }

  + #quill-toolbar {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
    border-bottom-left-radius: $border-radius;
    border-bottom-right-radius: $border-radius;
  }
}

.pointer-cursor {
  cursor: pointer;
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
@import '@core/scss/vue/libs/vue-select.scss';
@import '@core/scss/vue/libs/vue-flatpicker.scss';
@import '@core/scss/vue/libs/quill.scss';
</style>