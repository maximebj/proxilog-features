import { __ } from "@wordpress/i18n";
import {
  __experimentalText as Text,
  __experimentalHeading as Heading,
  __experimentalVStack as VStack,
  ToggleControl,
  Button,
  Notice,
} from "@wordpress/components";

import { useState, useEffect } from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";

export default function SettingsPage() {
  const [isEnabled, setIsEnabled] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [isSaving, setIsSaving] = useState(false);
  const [notice, setNotice] = useState(null);

  // Charger les paramètres au montage du composant
  useEffect(() => {
    loadSettings();
  }, []);

  const loadSettings = async () => {
    setIsLoading(true);
    try {
      const response = await apiFetch({
        path: "/proxilog-features/v1/settings",
        method: "GET",
      });
      setIsEnabled(response.isEnabled);
    } catch (error) {
      console.error("Error loading settings:", error);
      setNotice({
        type: "error",
        message: __("Failed to load settings", "proxilog-features"),
      });
    } finally {
      setIsLoading(false);
    }
  };

  const saveSettings = async () => {
    setIsSaving(true);
    setNotice(null);

    try {
      const response = await apiFetch({
        path: "/proxilog-features/v1/settings",
        method: "POST",
        data: {
          isEnabled: isEnabled,
        },
      });

      setNotice({
        type: "success",
        message:
          response.message ||
          __("Settings saved successfully", "proxilog-features"),
      });
    } catch (error) {
      console.error("Error saving settings:", error);
      setNotice({
        type: "error",
        message: __("Failed to save settings", "proxilog-features"),
      });
    } finally {
      setIsSaving(false);
    }
  };

  if (isLoading) {
    return (
      <VStack as="main" spacing={2} className="proxilog-content">
        <Text>{__("Loading settings...", "proxilog-features")}</Text>
      </VStack>
    );
  }

  return (
    <>
      <VStack as="header" spacing={2} className="proxilog-header">
        <Heading level={1}>Proxilog Features</Heading>
        <Text variant="muted">
          This is the settings page for the Proxilog Features plugin.
        </Text>
      </VStack>
      <VStack as="main" spacing={2} className="proxilog-content">
        {notice && (
          <Notice status={notice.type} onRemove={() => setNotice(null)}>
            {notice.message}
          </Notice>
        )}

        <ToggleControl
          __nextHasNoMarginBottom
          checked={isEnabled}
          label={__("Enable something", "proxilog-features")}
          help={__("This is a help text", "proxilog-features")}
          onChange={() => {
            setIsEnabled(!isEnabled);
          }}
        />

        <Button
          variant="primary"
          onClick={saveSettings}
          isBusy={isSaving}
          disabled={isSaving}
        >
          {isSaving
            ? __("Saving...", "proxilog-features")
            : __("Save Settings", "proxilog-features")}
        </Button>
      </VStack>
    </>
  );
}
