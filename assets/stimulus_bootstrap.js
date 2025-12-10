import { startStimulusApp } from '@symfony/stimulus-bundle';
import CheckboxSelectAll from '@stimulus-components/checkbox-select-all'

const app = startStimulusApp();

app.register('checkbox-select-all', CheckboxSelectAll)

// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
