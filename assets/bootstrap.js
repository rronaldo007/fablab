import { startStimulusApp } from '@symfony/stimulus-bridge';

const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]s$/
));

// Example: register controllers if needed
// app.register("hello", require("./controllers/hello_controller").default);
