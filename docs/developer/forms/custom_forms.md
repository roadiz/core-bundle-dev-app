---
title: Building custom forms
---

Building a custom form looks like building a node but it is a lot simpler! Let's have a look at structure image.

![image](./img/custom-form.svg){.align-center}

After creating a custom form, you add some question. The questions are the CustomFormField type.

The answer is saved in two entities:
- in CustomFormAnswer
- in CustomFormFieldAttribute

The CustomFormAnswer will store the IP and the submitted time.
While question answer will be in CustomFormFieldAttribute with the CustomFormAnswer id and the CustomFormField id.

## Exposing a custom form in your API

Custom-form can be filled in a headless context, using \_definition and \_post endpoints:

```http
GET {{baseUrl}}/api/custom_forms/:id/definition
```

Custom form definition is a **JSON form schema** meant to give your frontend application a recipe to build a HTML form:

```json
{
    "title": "",
    "type": "object",
    "properties": {
        "subject": {
            "type": "string",
            "title": "Subject",
            "attr": {
                "data-group": null,
                "placeholder": null
            },
            "description": "Est aut quas eum error architecto.",
            "propertyOrder": 1
        },
        "email": {
            "type": "string",
            "title": "Email",
            "attr": {
                "data-group": null,
                "placeholder": null
            },
            "description": "Email address",
            "widget": "email",
            "propertyOrder": 2
        },
        "test": {
            "title": "TEST",
            "type": "object",
            "properties": {
                "message": {
                    "type": "string",
                    "title": "Message",
                    "attr": {
                        "data-group": "TEST",
                        "placeholder": null
                    },
                    "widget": "textarea",
                    "propertyOrder": 1
                },
                "fichier": {
                    "type": "string",
                    "title": "File",
                    "attr": {
                        "data-group": "TEST",
                        "placeholder": null
                    },
                    "widget": "file",
                    "propertyOrder": 2
                }
            },
            "required": [
                "fichier"
            ],
            "attr": {
                "data-group-wrapper": "test"
            },
            "propertyOrder": 3
        }
    },
    "required": [
        "subject",
        "email",
        "test"
    ]
}
```

Then you can send your data to the **post** endpoint using *FormData* and respecting field hierarchy:

![image](./img/custom_form_post.png){.align-center}

```http
POST {{baseUrl}}/api/custom_forms/:id/post
```

If there are any error, a *JSON* response will give you details fields-by-fields.

If post is successful, API will respond an empty `202 Accepted` response

![image](./img/custom_form_post_response.png){.align-center}

Then you will be able to see all your form submits in Roadiz backoffice :

![image](./img/custom_form_entry.png){.align-left}

In Manage custom forms section / Answers

![image](./img/custom_form_answers.png){.align-center}

::: tip
Any file attached to your custom-form answers will be uploaded as private documents.
:::

![image](./img/custom_form_response.png){.align-center}

## Adding custom form to your theme

If you want to integrate your custom-forms into your theme, you can use Roadiz `CustomFormHelper` class to generate a standard `FormInterface` and to create a view into your theme templates.

First you must create a dedicated action for your node or your block if you used `nodeSource|render(@AwesomeTheme)` Twig filter.

```php
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;
use RZ\Roadiz\CoreBundle\Exception\ForceResponseException;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use RZ\Roadiz\CoreBundle\CustomForm\CustomFormHelper;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;

// …

/*
 * Get your custom form instance from your node-source
 * only if you added a *custom_form reference field*.
 */
$customForms = $this->nodeSource->getCustomFormReference();
if (isset($customForms[0]) && $customForms[0] instanceof CustomForm) {
    /** @var CustomForm $customForm */
    $customForm = $customForms[0];

    /*
     * Verify if custom form is still open
     * for answers
     */
    if ($customForm->isFormStillOpen()) {
        /*
         * CustomFormHelper will generate Symfony form against
         * Roadiz custom form entity.
         * You can add a Google Recaptcha passing following options.
         */
        $helper = $this->customFormHelperFactory->createHelper($customForm);
        $form = $helper->getForm($request, false, true);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $answer = $helper->parseAnswerFormData($form, null, $request->getClientIp());

                if ($request->isXmlHttpRequest()) {
                    $response = new JsonResponse([
                        'message' => $this->getTranslator()->trans('form_has_been_successfully_sent')
                    ]);
                } else {
                    $this->publishConfirmMessage(
                        $request,
                        $this->getTranslator()->trans('form_has_been_successfully_sent')
                    );
                    $response = $this->redirect($this->generateUrl(
                        RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                        [RouteObjectInterface::ROUTE_OBJECT => $this->nodeSource->getParent()]
                    ));
                }
                /*
                 * If you are in a BlockController use ForceResponseException
                 */
                throw new ForceResponseException($response);
                /*
                 * Or directly return redirect response.
                 */
                //return $response;
            } catch (EntityAlreadyExistsException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $this->assignation['form'] = $form->createView();
    }
}
```

If you didn't do it yet, create a custom form theme in your `views/` folder:

```twig
{#
 # AwesomeTheme/Resources/views/form.html.twig
 #}
{% extends "bootstrap_3_layout.html.twig" %}

{% block form_row -%}
    <div class="form-group form-group-{{ form.vars.block_prefixes[1] }} form-group-{{ form.vars.name }}">
        {% if form.vars.block_prefixes[1] != 'separator' %}
            {{- form_label(form) -}}
        {% endif %}
        {{- form_errors(form) -}}
        {#
         # Render field description inside your form
         #}
        {% if form.vars.attr['data-description'] %}
            <div class="form-description">
                {{ form.vars.attr['data-description']|markdown }}
            </div>
        {% endif %}
        {{- form_widget(form) -}}
    </div>
{%- endblock form_row %}

{% block recaptcha_widget -%}
   <input id="my-form-recaptcha" type="hidden" name="{{ form.vars.name }}" />
   <script src="https://www.google.com/recaptcha/api.js?render={{ configs.publicKey }}"></script>
   <script>
       /*
        * Google Recaptcha v3
        * @see https://developers.google.com/recaptcha/docs/v3
        */
       (function() {
           if (!window.grecaptcha) {
               console.warn('Recaptcha is not loaded');
           }
           var form = document.getElementById('my-form');
           form.addEventListener('submit', function (event) {
               event.preventDefault();
               window.grecaptcha.ready(function() {
                   window.grecaptcha.execute('{{ configs.publicKey }}', {action: 'submit'}).then(function(token) {
                       var input = document.getElementById('my-form-recaptcha');
                       if (input) {
                           input.value = token;
                       }
                       form.submit()
                   });
               });
           });
       })();
   </script>
{%- endblock recaptcha_widget %}
```

In your main view, add your form and use your custom form theme:

```twig
{#
 # AwesomeTheme/Resources/views/form-blocks/customformblock.html.twig
 #}
{% if form %}
    {% form_theme form '@AwesomeTheme/form.html.twig' %}
    {{ form_start(form) }}
    {{ form_widget(form) }}
    <div class="form-group">
        <button class="btn btn-primary" type="submit">{% trans %}send_form{% endtrans %}</button>
    </div>
    {{ form_end(form) }}
{% else %}
    <p class="alert alert-warning">{% trans %}form_is_not_available{% endtrans %}</p>
{% endif %}
```
