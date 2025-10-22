import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import {
  __experimentalText as Text,
  __experimentalHeading as Heading,
  __experimentalVStack as VStack,
  __experimentalHStack as HStack,
  ToggleControl,
  Button,
  Notice,
  Spinner,
} from "@wordpress/components";
import { useState, useEffect } from "@wordpress/element";
import { useSelect } from "@wordpress/data";

export default function SettingsPage() {
  const [settings, setSettings] = useState(null);
  const [showSnackbar, setShowSnackbar] = useState(false);
  // const [isLoading, setIsLoading] = useState(false);
  // const [isSaving, setIsSaving] = useState(false);
  // const [notice, setNotice] = useState(null);

  useEffect(() => {
    loadSettings();
  }, []);

  const loadSettings = async () => {
    try {
      const response = await apiFetch({
        path: "/proxilog-features/v1/settings",
        method: "GET",
      });

      setSettings(response);
    } catch (error) {
      console.error("Error loading settings:", error);
      // setNotice({
      //   type: "error",
      //   message: __("Failed to load settings", "proxilog-features"),
      // });
    }
  };

  console.log(settings);

  // const saveSettings = async () => {
  //   setIsSaving(true);
  //   setNotice(null);

  //   try {
  //     const response = await apiFetch({
  //       path: "/proxilog-features/v1/settings",
  //       method: "POST",
  //       data: {
  //         isEnabled: isEnabled,
  //       },
  //     });

  //     setNotice({
  //       type: "success",
  //       message:
  //         response.message ||
  //         __("Settings saved successfully", "proxilog-features"),
  //     });
  //   } catch (error) {
  //     console.error("Error saving settings:", error);
  //     setNotice({
  //       type: "error",
  //       message: __("Failed to save settings", "proxilog-features"),
  //     });
  //   } finally {
  //     setIsSaving(false);
  //   }
  // };

  // if (isLoading) {
  //   return (
  //     <VStack as="main" spacing={2} className="proxilog-content">
  //       <Text>{__("Loading settings...", "proxilog-features")}</Text>
  //     </VStack>
  //   );
  // }

  return (
    <>
      <HStack as="header" spacing={4} className="proxilog-header">
        <VStack spacing={2}>
          <Heading level={1}>Proxilog Features</Heading>
          <Text variant="muted">
            This is the settings page for the Proxilog Features plugin.
          </Text>
        </VStack>
        <Button
          variant="primary"
          // onClick={saveSettings}
          // isBusy={isSaving}
          // disabled={isSaving}
        >
          {__("Save Settings", "proxilog-features")}
          {/* {isSaving
            ? __("Saving...", "proxilog-features")
            : __("Save Settings", "proxilog-features")} */}
        </Button>
      </HStack>
      <VStack as="main" spacing={2} className="proxilog-content">
        {/* {notice && (
          <Notice status={notice.type} onRemove={() => setNotice(null)}>
            {notice.message}
          </Notice>
        )} */}
        {settings ? (
          <ToggleControl
            __nextHasNoMarginBottom
            checked={settings.isEnabled}
            label={__("Enable something", "proxilog-features")}
            help={__("This is a help text", "proxilog-features")}
            onChange={() => {
              setSettings({
                ...settings,
                isEnabled: !settings.isEnabled || false,
              });
            }}
          />
        ) : (
          <Spinner />
        )}
      </VStack>
    </>
  );
}
