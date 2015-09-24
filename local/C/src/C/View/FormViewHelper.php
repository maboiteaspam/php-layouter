<?php
namespace C\View;

use C\Layout\Block;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormView;

// @todo import from SF/Silex.
abstract class FormViewHelper implements ViewHelperInterface {

    /**
     * @var Block
     */
    public $block;

    public function setBlockToRender (Block $block) {
        $this->block = $block;
    }

    // form
    // vendor/symfony/twig-bridge/Extension/FormExtension.php
    // vendor/symfony/twig-bridge/Resources/views/Form/form_div_layout.html.twig
    public function form(FormView $form) {
        $this->form_start($form);
            $this->form_widget($form);
        $this->form_end($form);
    }
    public function form_start (FormView $form, $options=[]) {
        $vars = array_merge_recursive($form->vars, $options);

        $method = strtoupper($vars['method']);
        $name = ($vars['name']);
        $action = ($vars['action']);
        $attr = ($vars['attr']);
        $form_method = '';

        if (in_array($method, ['GET', 'POST',])) {
            $form_method = $method;
        } else {
            $form_method = 'POST';
        }

        echo "<form name='$name' method='".strtolower($form_method)."' action='$action' ";
        foreach ($attr as $name=>$val) {
            echo " $attr='$val' ";
        }
        $this->form_enctype($form, $options);
        echo " >";
        if ($form_method!==$method) {
            echo "<input type='hidden' name='_method' value='$method' />";
        }
    }
    public function form_widget (FormView $form, $options=[]) {
        $vars = array_merge_recursive($form->vars, $options);
        $compound = ($vars['compound']);

        if ($compound) {
            $this->form_widget_compound($form, $options);
        } else {
            $this->form_widget_simple($form, $options);
        }
    }
    public function form_widget_compound (FormView $form, $options=[]) {
        echo "<div";
        $this->widget_container_attributes($form, $options);
        if (!$form->parent) {
            $this->form_errors($form);
        }
        echo ">";
        $this->form_rows($form, $options);
        echo "</div>";
    }
    public function form_widget_simple (FormView $form, $options=[]) {
        $vars = array_merge_recursive($form->vars, $options);
        $type = $vars['type'] ? $vars['type'] : 'text';
        $val = $vars['value'];
        echo "<input ";
        echo "type='$type' ";
        $this->widget_attributes($form, $options);
        if ($val) {
            echo "value='$val' ";
        }
        echo "/>";
    }
    public function widget_attributes (FormView $form, $options=[]) {
        $vars = array_merge_recursive($form->vars, $options);
        $id = ($vars['id']);
        $full_name = ($vars['full_name']);
        $readonly = ($vars['read_only']);
        $disabled = ($vars['disabled']);
        $required = ($vars['required']);
        $attr = ($vars['attr']);
        echo "id='$id' ";
        echo "name='$full_name' ";
        if ($readonly && !array_key_exists('readonly', $attr)) echo "readonly='readonly'";
        if ($disabled) echo "disabled='disabled'";
        if ($required) echo "required='required'";
        foreach ($attr as $name=>$val) {
            if (in_array($name, ['placeholder', 'title',])) {
                echo " $attr='$val' "; // here should do translation.
            } else if ($val==true) { // triple eq ? or double eq ?
                echo " $attr='$attr' ";
            } else if ($val!=false) { // triple eq ? or double eq ?
                echo " $attr='$val' ";
            }
        }
    }
    public function widget_container_attributes (FormView $form, $options=[]) {
        $vars = array_merge_recursive($form->vars, $options);
        $id = ($vars['id']);
        $attr = ($vars['attr']);

        if ($id) {
            echo "id='$id'";
        }
        foreach ($attr as $name=>$val) {
            if (in_array($name, ['placeholder', 'title',])) {
                echo " $attr='$val' "; // here should do translation.
            } else if ($val==true) { // triple eq ? or double eq ?
                echo " $attr='$attr' ";
            } else if ($val!=false) { // triple eq ? or double eq ?
                echo " $attr='$val' ";
            }
        }
    }

    public function form_end (FormView $form, $options=[]) {
        $vars = array_merge_recursive($form->vars, $options);
        if (!array_key_exists('render_rest', $vars) || $vars['render_rest']) {
            $this->form_rest($form);
        }
        echo "</form>";
    }

    public function form_enctype (FormView $form, $options=[]) {
        $vars = array_merge_recursive($form->vars, $options);
        $multipart = ($vars['multipart']);
        if ($multipart) {
            echo " enctype='multipart/form-data' ";
        }
    }

    public function form_errors (FormView $form, $options=[]) {
        $vars = array_merge_recursive($form->vars, $options);
        $errors = $vars['errors'];
        if (count($errors)) {
            echo "<ul>";
            foreach ($errors as $error) {
                echo "<li>";
                echo $error->message;
                echo "</ul>";
            }
            echo "</ul>";
        }
    }

    public function form_label (FormView $form, $options=[]) {
        $vars = array_merge_recursive($form->vars, $options);
        $label = $vars['label'];
        $id = $vars['id'];
        $compound = $vars['compound'];
        $label_attr = $vars['label_attr'];
        $name = $vars['name'];
        $required = $vars['required'];
        $label_format = $vars['label_format'];
        $translation_domain = $vars['translation_domain'];

        if ($label!==false) {
            if ($compound!==false) {
                $label_attr = array_merge(['for'=>$id], $label_attr);
            }
            if ($required) {
                $label_attr = array_merge(['class' => ''], $label_attr);
                $label_attr['class'] .= ' required';
            }

            if (!$label) {
                if ($label_format) {
                    $label = str_replace(['%name%', '%id%',], [$name, $id,], $label_format);
                } else {
                    // needs CommonViewHelper
                    /*
                     *
                {% set label = name|humanize %}
                     */
                }

            }

            echo "<label ";
            foreach ($label_attr as $name=>$val) {
                echo "$name='$val' ";
            }
            echo ">";
            echo $translation_domain ? "translate($label)" : "$label" ;
            echo "</label>";
        }
    }
    public function form_row (FormView $form, $options=[]) {
        echo "<div>";
        $this->form_label($form, $options);
        $this->form_errors($form, $options);
        $this->form_widget($form, $options);
        echo "</div>";
    }
    public function form_rows (FormView $form, $options=[]) {
        foreach ($form->children as $child) {
            $this->form_row($child, $options);
        }
    }
    public function form_rest (FormView $form, $options=[]) {
        foreach ($form->children as $child) {
            if (!$child->isRendered()) {
                $this->form_row($child, $options);
            }
        }
    }
    /*
     *
{%- block collection_widget -%}
    {% if prototype is defined %}
        {%- set attr = attr|merge({'data-prototype': form_row(prototype) }) -%}
    {% endif %}
    {{- block('form_widget') -}}
{%- endblock collection_widget -%}

{%- block textarea_widget -%}
    <textarea {{ block('widget_attributes') }}>{{ value }}</textarea>
{%- endblock textarea_widget -%}

{%- block choice_widget -%}
    {% if expanded %}
        {{- block('choice_widget_expanded') -}}
    {% else %}
        {{- block('choice_widget_collapsed') -}}
    {% endif %}
{%- endblock choice_widget -%}

{%- block choice_widget_expanded -%}
    <div {{ block('widget_container_attributes') }}>
    {%- for child in form %}
        {{- form_widget(child) -}}
        {{- form_label(child, null, {translation_domain: choice_translation_domain}) -}}
    {% endfor -%}
    </div>
{%- endblock choice_widget_expanded -%}

{%- block choice_widget_collapsed -%}
    {%- if required and placeholder is none and not placeholder_in_choices and not multiple -%}
        {% set required = false %}
    {%- endif -%}
    <select {{ block('widget_attributes') }}{% if multiple %} multiple="multiple"{% endif %}>
        {%- if placeholder is not none -%}
            <option value=""{% if required and value is empty %} selected="selected"{% endif %}>{{ placeholder != '' ? placeholder|trans({}, translation_domain) }}</option>
        {%- endif -%}
        {%- if preferred_choices|length > 0 -%}
            {% set options = preferred_choices %}
            {{- block('choice_widget_options') -}}
            {%- if choices|length > 0 and separator is not none -%}
                <option disabled="disabled">{{ separator }}</option>
            {%- endif -%}
        {%- endif -%}
        {%- set options = choices -%}
        {{- block('choice_widget_options') -}}
    </select>
{%- endblock choice_widget_collapsed -%}

{%- block choice_widget_options -%}
    {% for group_label, choice in options %}
        {%- if choice is iterable -%}
            <optgroup label="{{ choice_translation_domain is same as(false) ? group_label : group_label|trans({}, choice_translation_domain) }}">
                {% set options = choice %}
                {{- block('choice_widget_options') -}}
            </optgroup>
        {%- else -%}
            {% set attr = choice.attr %}
            <option value="{{ choice.value }}" {{ block('attributes') }}{% if choice is selectedchoice(value) %} selected="selected"{% endif %}>{{ choice_translation_domain is same as(false) ? choice.label : choice.label|trans({}, choice_translation_domain) }}</option>
        {%- endif -%}
    {% endfor %}
{%- endblock choice_widget_options -%}

{%- block checkbox_widget -%}
    <input type="checkbox" {{ block('widget_attributes') }}{% if value is defined %} value="{{ value }}"{% endif %}{% if checked %} checked="checked"{% endif %} />
{%- endblock checkbox_widget -%}

{%- block radio_widget -%}
    <input type="radio" {{ block('widget_attributes') }}{% if value is defined %} value="{{ value }}"{% endif %}{% if checked %} checked="checked"{% endif %} />
{%- endblock radio_widget -%}

{%- block datetime_widget -%}
    {% if widget == 'single_text' %}
        {{- block('form_widget_simple') -}}
    {%- else -%}
        <div {{ block('widget_container_attributes') }}>
            {{- form_errors(form.date) -}}
            {{- form_errors(form.time) -}}
            {{- form_widget(form.date) -}}
            {{- form_widget(form.time) -}}
        </div>
    {%- endif -%}
{%- endblock datetime_widget -%}

{%- block date_widget -%}
    {%- if widget == 'single_text' -%}
        {{ block('form_widget_simple') }}
    {%- else -%}
        <div {{ block('widget_container_attributes') }}>
            {{- date_pattern|replace({
                '{{ year }}':  form_widget(form.year),
                '{{ month }}': form_widget(form.month),
                '{{ day }}':   form_widget(form.day),
            })|raw -}}
        </div>
    {%- endif -%}
{%- endblock date_widget -%}

{%- block time_widget -%}
    {%- if widget == 'single_text' -%}
        {{ block('form_widget_simple') }}
    {%- else -%}
        {%- set vars = widget == 'text' ? { 'attr': { 'size': 1 }} : {} -%}
        <div {{ block('widget_container_attributes') }}>
            {{ form_widget(form.hour, vars) }}{% if with_minutes %}:{{ form_widget(form.minute, vars) }}{% endif %}{% if with_seconds %}:{{ form_widget(form.second, vars) }}{% endif %}
        </div>
    {%- endif -%}
{%- endblock time_widget -%}

{%- block number_widget -%}
    {# type="number" doesn't work with floats #}
    {%- set type = type|default('text') -%}
    {{ block('form_widget_simple') }}
{%- endblock number_widget -%}

{%- block integer_widget -%}
    {%- set type = type|default('number') -%}
    {{ block('form_widget_simple') }}
{%- endblock integer_widget -%}

{%- block money_widget -%}
    {{ money_pattern|replace({ '{{ widget }}': block('form_widget_simple') })|raw }}
{%- endblock money_widget -%}

{%- block url_widget -%}
    {%- set type = type|default('url') -%}
    {{ block('form_widget_simple') }}
{%- endblock url_widget -%}

{%- block search_widget -%}
    {%- set type = type|default('search') -%}
    {{ block('form_widget_simple') }}
{%- endblock search_widget -%}

{%- block percent_widget -%}
    {%- set type = type|default('text') -%}
    {{ block('form_widget_simple') }} %
{%- endblock percent_widget -%}

{%- block password_widget -%}
    {%- set type = type|default('password') -%}
    {{ block('form_widget_simple') }}
{%- endblock password_widget -%}

{%- block hidden_widget -%}
    {%- set type = type|default('hidden') -%}
    {{ block('form_widget_simple') }}
{%- endblock hidden_widget -%}

{%- block email_widget -%}
    {%- set type = type|default('email') -%}
    {{ block('form_widget_simple') }}
{%- endblock email_widget -%}

{%- block button_widget -%}
    {%- if label is empty -%}
        {%- if label_format is not empty -%}
            {% set label = label_format|replace({
                '%name%': name,
                '%id%': id,
            }) %}
        {%- else -%}
            {% set label = name|humanize %}
        {%- endif -%}
    {%- endif -%}
    <button type="{{ type|default('button') }}" {{ block('button_attributes') }}>{{ label|trans({}, translation_domain) }}</button>
{%- endblock button_widget -%}

{%- block submit_widget -%}
    {%- set type = type|default('submit') -%}
    {{ block('button_widget') }}
{%- endblock submit_widget -%}

{%- block reset_widget -%}
    {%- set type = type|default('reset') -%}
    {{ block('button_widget') }}
{%- endblock reset_widget -%}
     */
    public function csrf_token() {
        // must return the token value.
    }

}
