import { __ } from "@wordpress/i18n";
import {
  __experimentalText as Text,
  __experimentalHeading as Heading,
  __experimentalVStack as VStack,
  ToggleControl,
} from "@wordpress/components";

import { useState } from "@wordpress/element";

export default function SettingsPage() {
  const [isEnabled, setIsEnabled] = useState(false);

  return (
    <>
      <VStack as="header" spacing={2} className="proxilog-header">
        <Heading level={1}>Proxilog Features</Heading>
        <Text variant="muted">
          This is the settings page for the Proxilog Features plugin.
        </Text>
      </VStack>
      <VStack as="main" spacing={2} className="proxilog-content">
        <ToggleControl
          __nextHasNoMarginBottom
          checked={isEnabled}
          label={__("Enable something", "proxilog-features")}
          help={__("This is a help text", "proxilog-features")}
          onChange={() => {
            setIsEnabled(!isEnabled);
          }}
        />
      </VStack>
    </>
  );
}
