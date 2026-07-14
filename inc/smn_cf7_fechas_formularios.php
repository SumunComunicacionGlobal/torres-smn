<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 
 * Description: Limita fechas y horas en Contact Form 7 según clínica, día de la semana y días no laborables.
 * Version: 1.0.3
 */

if (!defined('ABSPATH')) exit;

/**
 * CONFIGURACIÓN
 */
function cf7cc_get_clinics() {
    return [
        'Cualquier clínica',
        'Torres Independencia',
        'Torres San José',
        'Torres Roma',
    ];
}

function cf7cc_get_weekdays() {
    return [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
    ];
}

/**
 * ADMIN
 */
add_action('admin_menu', function () {
    add_options_page(
        'CF7 Citas Clínicas',
        'CF7 Citas Clínicas',
        'manage_options',
        'cf7-citas-clinicas',
        'cf7cc_settings_page'
    );
});

add_action('admin_init', function () {
    register_setting('cf7cc_group', 'cf7cc_dias_no_laborables');

    foreach (cf7cc_get_clinics() as $clinic) {
        foreach (cf7cc_get_weekdays() as $day_number => $weekday) {
            register_setting(
                'cf7cc_group',
                'cf7cc_hours_' . sanitize_title($clinic) . '_' . $day_number
            );
        }
    }
});

function cf7cc_settings_page() {
    $clinics = cf7cc_get_clinics();
    $weekdays = cf7cc_get_weekdays();
    ?>
    <div class="wrap">
        <h1>CF7 Citas Clínicas</h1>

        <form method="post" action="options.php">
            <?php settings_fields('cf7cc_group'); ?>

            <h2>Días no laborables</h2>
            <p>Introduce un día por línea en formato <strong>DD/MM/AAAA</strong>.</p>

            <textarea name="cf7cc_dias_no_laborables" rows="8" style="width:420px;"><?php
                echo esc_textarea(get_option('cf7cc_dias_no_laborables', ''));
            ?></textarea>

            <hr>

            <h2>Horarios por clínica y día</h2>
            <p>Introduce horas enteras separadas por comas. Ejemplo: <strong>9,10,11,12,16,17,18</strong></p>

            <table class="widefat striped" style="max-width:1000px;">
                <thead>
                    <tr>
                        <th>Clínica</th>
                        <?php foreach ($weekdays as $weekday): ?>
                            <th><?php echo esc_html($weekday); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($clinics as $clinic): ?>
                        <tr>
                            <td><strong><?php echo esc_html($clinic); ?></strong></td>

                            <?php foreach ($weekdays as $day_number => $weekday):
                                $option_name = 'cf7cc_hours_' . sanitize_title($clinic) . '_' . $day_number;
                                ?>
                                <td>
                                    <input
                                        type="text"
                                        name="<?php echo esc_attr($option_name); ?>"
                                        value="<?php echo esc_attr(get_option($option_name, '')); ?>"
                                        placeholder="9,10,11,12"
                                        style="width:100%;"
                                    >
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * DÍAS NO LABORABLES
 * Admin: DD/MM/AAAA
 * Interno: YYYY-MM-DD
 */
function cf7cc_get_disabled_dates() {
    $raw = get_option('cf7cc_dias_no_laborables', '');
    $lines = preg_split('/\r\n|\r|\n/', $raw);
    $dates = [];

    foreach ($lines as $line) {
        $line = trim($line);

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $line, $m)) {
            $dates[] = "{$m[3]}-{$m[2]}-{$m[1]}";
        }
    }

    return array_values(array_unique($dates));
}

/**
 * HORAS DISPONIBLES
 */
function cf7cc_get_available_hours() {
    $data = [];

    foreach (cf7cc_get_clinics() as $clinic) {
        $data[$clinic] = [];

        foreach (cf7cc_get_weekdays() as $day_number => $weekday) {
            $option_name = 'cf7cc_hours_' . sanitize_title($clinic) . '_' . $day_number;
            $raw = get_option($option_name, '');

            $hours = array_filter(array_map('trim', explode(',', $raw)), function ($hour) {
                return preg_match('/^\d{1,2}$/', $hour) && (int) $hour >= 0 && (int) $hour <= 23;
            });

            $hours = array_map(function ($hour) {
                return str_pad((int) $hour, 2, '0', STR_PAD_LEFT) . ':00';
            }, $hours);

            $data[$clinic][$day_number] = array_values(array_unique($hours));
        }
    }

    return $data;
}

/**
 * CARGAR FLATPICKR
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'flatpickr',
        'https://cdn.jsdelivr.net/npm/flatpickr',
        [],
        null,
        true
    );

    wp_enqueue_script(
        'flatpickr-es',
        'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js',
        ['flatpickr'],
        null,
        true
    );

    wp_enqueue_style(
        'flatpickr-css',
        'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css'
    );
});

/**
 * FRONTEND
 */
add_action('wp_footer', function () {
    $timezone = wp_timezone();
    $today = new DateTime('today', $timezone);

    $min = clone $today;
    $min->modify('+1 day');

    $max = clone $today;
    $max->modify('+30 days');

    $disabled_dates = cf7cc_get_disabled_dates();
    $available_hours = cf7cc_get_available_hours();
    ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const clinicField = document.querySelector('[name="clinica"]');
    const dateField = document.querySelector('[name="your-date"]');
    const timeField = document.querySelector('[name="your-time"]');

    if (!clinicField || !dateField || !timeField) return;

    const minDate = "<?php echo esc_js($min->format('d/m/Y')); ?>";
    const maxDate = "<?php echo esc_js($max->format('d/m/Y')); ?>";
    const disabledDates = <?php echo wp_json_encode($disabled_dates); ?>;
    const availableHours = <?php echo wp_json_encode($available_hours); ?>;
    const anyClinicLabel = 'Cualquier clínica';
	
	console.group('CF7 Citas Clínicas - Debug');

	console.log('Fecha mínima:', minDate);
	console.log('Fecha máxima:', maxDate);
	console.log('Días deshabilitados:', disabledDates);
	console.log('Horas disponibles:', availableHours);

	console.log('Campo clínica:', clinicField);
	console.log('Valor clínica inicial:', clinicField ? clinicField.value : null);

	console.log('Campo fecha:', dateField);
	console.log('Valor fecha inicial:', dateField ? dateField.value : null);

	console.log('Campo hora:', timeField);
	console.log('Valor hora inicial:', timeField ? timeField.value : null);

	console.groupEnd();

    flatpickr(dateField, {
        locale: "es",
        dateFormat: "d/m/Y",
        minDate: minDate,
        maxDate: maxDate,
        disableMobile: true,
        allowInput: false,

		disable: [
			function(date) {
				const y = date.getFullYear();
				const m = String(date.getMonth() + 1).padStart(2, '0');
				const d = String(date.getDate()).padStart(2, '0');
				const formatted = `${y}-${m}-${d}`;

				const isWeekend = date.getDay() === 0 || date.getDay() === 6;
				const isDisabledDate = disabledDates.includes(formatted);
				const disabled = isWeekend || isDisabledDate;

				console.log('Flatpickr evalúa:', {
					date: formatted,
					day: date.getDay(),
					isWeekend,
					isDisabledDate,
					disabled
				});

				return disabled;
			}
		],
        onChange: function() {
            handleDateChange();
        }
    });

    function resetTimeField(message = 'Selecciona hora') {
        timeField.innerHTML = '';

        const option = document.createElement('option');
        option.value = '';
        option.textContent = message;

        timeField.appendChild(option);
    }

    function disableDateField() {
        dateField.disabled = true;
        dateField.value = '';

        if (dateField._flatpickr) {
            dateField._flatpickr.clear();
        }
    }

    function enableDateField() {
        dateField.disabled = false;
    }

    function disableTimeField(message = 'Selecciona hora') {
        timeField.disabled = true;
        resetTimeField(message);
    }

    function enableTimeField() {
        timeField.disabled = false;
    }

    function parseFrontDate(dateValue) {
        const parts = dateValue.split('/');

        if (parts.length !== 3) return null;

        const day = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1;
        const year = parseInt(parts[2], 10);

        if (!day || month < 0 || !year) return null;

        return new Date(year, month, day);
    }

    function updateAvailableHours() {
        const clinic = clinicField.value;
        const dateValue = dateField.value;

		console.group('CF7 Citas Clínicas - updateAvailableHours');
		console.log('Clínica seleccionada:', clinic);
		console.log('Fecha seleccionada:', dateValue);
		
        resetTimeField();

        if (!clinic || !dateValue) {
            disableTimeField();
            return;
        }

        const date = parseFrontDate(dateValue);
		console.log('Fecha parseada JS:', date);

        if (!date) {
            disableTimeField('Fecha no válida');
            return;
        }

        const weekday = date.getDay();
		console.log('Día JS getDay:', weekday);

        if (weekday === 0 || weekday === 6) {
            disableTimeField('Sin horas disponibles');
            return;
        }

        let hours = [];
        if (clinic === anyClinicLabel) {
            const configuredAny = availableHours?.[anyClinicLabel]?.[weekday] || [];

            if (configuredAny.length) {
                hours = configuredAny;
            } else {
                const merged = [];
                Object.keys(availableHours || {}).forEach(function (clinicName) {
                    if (clinicName === anyClinicLabel) {
                        return;
                    }

                    const clinicHours = availableHours?.[clinicName]?.[weekday] || [];
                    merged.push.apply(merged, clinicHours);
                });

                hours = Array.from(new Set(merged)).sort();
            }
        } else {
            hours = availableHours?.[clinic]?.[weekday] || [];
        }
		console.log('Horas encontradas:', hours);
		console.groupEnd();

        if (!hours.length) {
            disableTimeField('Sin horas disponibles');
            return;
        }

        resetTimeField('Selecciona hora');

        hours.forEach(function(hour) {
            const option = document.createElement('option');
            option.value = hour;
            option.textContent = hour;
            timeField.appendChild(option);
        });

        enableTimeField();
    }

    function handleClinicChange() {
        disableDateField();
        disableTimeField();

        if (clinicField.value) {
            enableDateField();
        }
    }

    function handleDateChange() {
        disableTimeField();

        if (dateField.value) {
            updateAvailableHours();
        }
    }

    disableDateField();
    disableTimeField();

    clinicField.addEventListener('change', handleClinicChange);
    dateField.addEventListener('change', handleDateChange);

    if (clinicField.value) {
        enableDateField();
    }
});
</script>

<?php
});

/**
 * VALIDACIÓN FECHA
 */
add_filter('wpcf7_validate_text', 'cf7cc_validate_date', 20, 2);
add_filter('wpcf7_validate_text*', 'cf7cc_validate_date', 20, 2);

function cf7cc_validate_date($result, $tag) {
    if ($tag->name !== 'your-date') {
        return $result;
    }

    $value = isset($_POST['your-date']) ? sanitize_text_field($_POST['your-date']) : '';

    if (!$value) {
        $result->invalidate($tag, 'Selecciona una fecha válida.');
        return $result;
    }

    $timezone = wp_timezone();

    $selected = DateTime::createFromFormat('d/m/Y', $value, $timezone);
    $errors = DateTime::getLastErrors();

    if (!$selected || !empty($errors['warning_count']) || !empty($errors['error_count'])) {
        $result->invalidate($tag, 'La fecha seleccionada no es válida.');
        return $result;
    }

    $today = new DateTime('today', $timezone);

    $min = clone $today;
    $min->modify('+1 day');

    $max = clone $today;
    $max->modify('+30 days');

    $weekday = (int) $selected->format('N');

    if ($selected < $min || $selected > $max) {
        $result->invalidate($tag, 'La fecha debe estar entre mañana y los próximos 30 días.');
        return $result;
    }

    if ($weekday > 5) {
        $result->invalidate($tag, 'Solo se pueden seleccionar días de lunes a viernes.');
        return $result;
    }

    if (in_array($selected->format('Y-m-d'), cf7cc_get_disabled_dates(), true)) {
        $result->invalidate($tag, 'La fecha seleccionada no está disponible.');
        return $result;
    }

    return $result;
}

/**
 * VALIDACIÓN HORA
 */
add_filter('wpcf7_validate_select', 'cf7cc_validate_time', 20, 2);
add_filter('wpcf7_validate_select*', 'cf7cc_validate_time', 20, 2);

function cf7cc_validate_time($result, $tag) {
    if ($tag->name !== 'your-time') {
        return $result;
    }

    $clinic = isset($_POST['clinica']) ? sanitize_text_field($_POST['clinica']) : '';
    $date = isset($_POST['your-date']) ? sanitize_text_field($_POST['your-date']) : '';
    $time = isset($_POST['your-time']) ? sanitize_text_field($_POST['your-time']) : '';

    if (!$clinic || !$date || !$time) {
        $result->invalidate($tag, 'Selecciona una hora válida.');
        return $result;
    }

    $selected_date = DateTime::createFromFormat('d/m/Y', $date, wp_timezone());
    $errors = DateTime::getLastErrors();

    if (!$selected_date || !empty($errors['warning_count']) || !empty($errors['error_count'])) {
        $result->invalidate($tag, 'La fecha seleccionada no es válida.');
        return $result;
    }

    $weekday = (int) $selected_date->format('N');
    $available_hours = cf7cc_get_available_hours();
    $valid_hours = cf7cc_get_valid_hours_for_clinic_day($clinic, $weekday, $available_hours);

    if (!in_array($time, $valid_hours, true)) {
        $result->invalidate($tag, 'La hora seleccionada no está disponible para esa clínica y fecha.');
        return $result;
    }

    return $result;
}