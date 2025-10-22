import domReady from "@wordpress/dom-ready";
import { createRoot } from "@wordpress/element";

import SettingsPage from "./SettingsPage";

import "./style.scss";

domReady(() => {
  const root = createRoot(document.getElementById("proxilog-features-root"));

  root.render(<SettingsPage />);
});
