<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'Le champ :attribute doit être accepté.',
    'accepted_if'          => 'Le champ :attribute doit être accepté lorsque :other vaut :value.',
    'active_url'           => 'Le champ :attribute n\'est pas une URL valide.',
    'after'                => 'Le champ :attribute doit être une date postérieure au :date.',
    'after_or_equal'       => 'Le champ :attribute doit être une date postérieure ou égale au :date.',
    'alpha'                => 'Le champ :attribute doit contenir uniquement des lettres.',
    'alpha_dash'           => 'Le champ :attribute doit contenir uniquement des lettres, des chiffres, des tirets et des soulignés.',
    'alpha_num'            => 'Le champ :attribute doit contenir uniquement des lettres et des chiffres.',
    'array'                => 'Le champ :attribute doit être un tableau.',
    'before'               => 'Le champ :attribute doit être une date antérieure au :date.',
    'before_or_equal'      => 'Le champ :attribute doit être une date antérieure ou égale au :date.',
    'between'              => [
        'array'   => 'Le champ :attribute doit contenir entre :min et :max éléments.',
        'file'    => 'Le champ :attribute doit être compris entre :min et :max kilo-octets.',
        'numeric' => 'Le champ :attribute doit être compris entre :min et :max.',
        'string'  => 'Le champ :attribute doit contenir entre :min et :max caractères.',
    ],
    'boolean'              => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed'            => 'La confirmation du champ :attribute ne correspond pas.',
    'current_password'     => 'Le mot de passe est incorrect.',
    'date'                 => 'Le champ :attribute n\'est pas une date valide.',
    'date_equals'          => 'Le champ :attribute doit être une date égale à :date.',
    'date_format'          => 'Le champ :attribute ne correspond pas au format :format.',
    'declined'             => 'Le champ :attribute doit être refusé.',
    'declined_if'          => 'Le champ :attribute doit être refusé lorsque :other vaut :value.',
    'different'            => 'Le champ :attribute et :other doivent être différents.',
    'digits'               => 'Le champ :attribute doit contenir :digits chiffres.',
    'digits_between'       => 'Le champ :attribute doit contenir entre :min et :max chiffres.',
    'dimensions'           => 'Le champ :attribute a des dimensions d\'image invalides.',
    'distinct'             => 'Le champ :attribute a une valeur en double.',
    'email'                => 'Le champ :attribute doit être une adresse e-mail valide.',
    'ends_with'            => 'Le champ :attribute doit se terminer par l\'une des valeurs suivantes : :values.',
    'enum'                 => 'L\' :attribute sélectionné est invalide.',
    'exists'               => 'L\' :attribute sélectionné est invalide.',
    'file'                 => 'Le champ :attribute doit être un fichier.',
    'filled'               => 'Le champ :attribute doit avoir une valeur.',
    'gt'                   => [
        'array'   => 'Le champ :attribute doit contenir plus de :value éléments.',
        'file'    => 'Le champ :attribute doit être supérieur à :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être supérieur à :value.',
        'string'  => 'Le champ :attribute doit contenir plus de :value caractères.',
    ],
    'gte'                  => [
        'array'   => 'Le champ :attribute doit contenir :value éléments ou plus.',
        'file'    => 'Le champ :attribute doit être supérieur ou égal à :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être supérieur ou égal à :value.',
        'string'  => 'Le champ :attribute doit contenir :value caractères ou plus.',
    ],
    'image'                => 'Le champ :attribute doit être une image.',
    'in'                   => 'L\' :attribute sélectionné est invalide.',
    'in_array'             => 'Le champ :attribute n\'existe pas dans :other.',
    'integer'              => 'Le champ :attribute doit être un entier.',
    'ip'                   => 'Le champ :attribute doit être une adresse IP valide.',
    'ipv4'                 => 'Le champ :attribute doit être une adresse IPv4 valide.',
    'ipv6'                 => 'Le champ :attribute doit être une adresse IPv6 valide.',
    'json'                 => 'Le champ :attribute doit être une chaîne JSON valide.',
    'lt'                   => [
        'array'   => 'Le champ :attribute doit contenir moins de :value éléments.',
        'file'    => 'Le champ :attribute doit être inférieur à :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être inférieur à :value.',
        'string'  => 'Le champ :attribute doit contenir moins de :value caractères.',
    ],
    'lte'                  => [
        'array'   => 'Le champ :attribute ne doit pas contenir plus de :value éléments.',
        'file'    => 'Le champ :attribute doit être inférieur ou égal à :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être inférieur ou égal à :value.',
        'string'  => 'Le champ :attribute doit contenir :value caractères ou moins.',
    ],
    'mac_address'          => 'Le champ :attribute doit être une adresse MAC valide.',
    'max'                  => [
        'array'   => 'Le champ :attribute ne doit pas contenir plus de :max éléments.',
        'file'    => 'Le champ :attribute ne doit pas être supérieur à :max kilo-octets.',
        'numeric' => 'Le champ :attribute ne doit pas être supérieur à :max.',
        'string'  => 'Le champ :attribute ne doit pas contenir plus de :max caractères.',
    ],
    'mimes'                => 'Le champ :attribute doit être un fichier de type : :values.',
    'mimetypes'            => 'Le champ :attribute doit être un fichier de type : :values.',
    'min'                  => [
        'array'   => 'Le champ :attribute doit contenir au moins :min éléments.',
        'file'    => 'Le champ :attribute doit être d\'au moins :min kilo-octets.',
        'numeric' => 'Le champ :attribute doit être d\'au moins :min.',
        'string'  => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'multiple_of'          => 'Le champ :attribute doit être un multiple de :value.',
    'not_in'               => 'L\' :attribute sélectionné est invalide.',
    'not_regex'            => 'Le format du champ :attribute est invalide.',
    'numeric'              => 'Le champ :attribute doit être un nombre.',
    'password'             => 'Le mot de passe est incorrect.',
    'present'              => 'Le champ :attribute doit être présent.',
    'prohibited'           => 'Le champ :attribute est interdit.',
    'prohibited_if'        => 'Le champ :attribute est interdit lorsque :other vaut :value.',
    'prohibited_unless'    => 'Le champ :attribute est interdit sauf si :other est dans :values.',
    'prohibits'            => 'Le champ :attribute interdit la présence de :other.',
    'regex'                => 'Le format du champ :attribute est invalide.',
    'required'             => 'Le champ :attribute est obligatoire.',
    'required_array_keys'  => 'Le champ :attribute doit contenir des entrées pour : :values.',
    'required_if'          => 'Le champ :attribute est obligatoire lorsque :other vaut :value.',
    'required_unless'      => 'Le champ :attribute est obligatoire sauf si :other est dans :values.',
    'required_with'        => 'Le champ :attribute est obligatoire lorsque :values est présent.',
    'required_with_all'    => 'Le champ :attribute est obligatoire lorsque :values sont présents.',
    'required_without'     => 'Le champ :attribute est obligatoire lorsque :values n\'est pas présent.',
    'required_without_all' => 'Le champ :attribute est obligatoire lorsqu\'aucune des :values n\'est présente.',
    'same'                 => 'Le champ :attribute et :other doivent correspondre.',
    'size'                 => [
        'array'   => 'Le champ :attribute doit contenir :size éléments.',
        'file'    => 'Le champ :attribute doit être de :size kilo-octets.',
        'numeric' => 'Le champ :attribute doit être :size.',
        'string'  => 'Le champ :attribute doit contenir :size caractères.',
    ],
    'starts_with'          => 'Le champ :attribute doit commencer par l\'une des valeurs suivantes : :values.',
    'string'               => 'Le champ :attribute doit être une chaîne de caractères.',
    'timezone'             => 'Le champ :attribute doit être un fuseau horaire valide.',
    'unique'               => 'L\' :attribute a déjà été pris.',
    'uploaded'             => 'Le téléchargement du champ :attribute a échoué.',
    'url'                  => 'Le champ :attribute doit être une URL valide.',
    'uuid'                 => 'Le champ :attribute doit être un UUID valide.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
